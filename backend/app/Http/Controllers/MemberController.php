<?php
namespace App\Http\Controllers;

use App\Http\Requests\Member\MembershipRenewRequest;
use App\Http\Requests\Member\MembershipUpdateRequest;
use App\Http\Requests\Member\StoreRequest;
use App\Http\Requests\Member\UpdateRequest;
use App\Jobs\SendWhatsappMessageJob;
use App\Models\Member;
use App\Models\Membership;
use App\Models\Payment;
use App\Templates\Whatsapp\Messages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class MemberController extends Controller
{
    public function dropdownList()
    {
        $model = Member::query();
        $model->when(request()->filled('plan_id'), fn($q) => $q->where('plan_id', request('plan_id')));
        $model->orderBy(request('order_by') ?? "id", request('sort_by_desc') ? "desc" : "asc");
        return $model->get();
    }

    public function getNextSystemUserId()
    {
        return (Member::max("system_user_id") ?? 1000) + 1;
    }

    public function index(Member $member, Request $request)
    {
        return $member->filterV1($request)->paginate(10);
    }

    public function store(StoreRequest $request)
    {
        $payload = collect($request->validated())->map(function ($value) {
            return ($value === 'null' || $value === '') ? null : $value;
        });

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $payload['profile_picture'] = $this->uploadProfilePicture($request->file('profile_picture'));
        }

        DB::beginTransaction();

        try {
            // Create member
            $member = Member::create($payload->toArray());

            // Attach plan
            $this->attachMembershipPlan($member, $payload);

            // Record payment
            $this->recordPayment($member, $payload, $request);

            DB::commit();

            $member->profile_picture = asset('media/member/profile_picture/' . $member->profile_picture);

            SendWhatsappMessageJob::dispatch($member->whatsapp_number, Messages::TEMPLATES[Messages::ACCOUNT_CREATED]);

            return $this->response('Member successfully created.', $member, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response($e->getMessage(), null, false);
        }
    }

    public function memberUpdate(UpdateRequest $request, $id)
    {
        $data = $request->validated();

        $member = Member::where("id", $id)->first();

        if ($request->profile_picture && $request->hasFile('profile_picture')) {
            $file     = $request->file('profile_picture');
            $ext      = $file->getClientOriginalExtension();
            $fileName = time() . '.' . $ext;
            $request->profile_picture->move(public_path('media/member/profile_picture/'), $fileName);
            $data['profile_picture'] = $fileName;
        }

        try {
            $updated = $member->update($data);


            if (! $updated) {
                return $this->response('Member cannot update.', null, false);
            }

            $member->refresh(); // reloads the latest DB values

            SendWhatsappMessageJob::dispatch($member->whatsapp_number, Messages::TEMPLATES[Messages::ACCOUNT_UPDATED]);

            return $this->response('Member Details successfully updated.', $member, true);
        } catch (\Exception $e) {
            return $this->response('Member Details successfully updated.',$e->getMessage(), true);
        }
    }

    public function memberShipUpdate(MembershipUpdateRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction(); // ensure atomicity
        try {
            $memberShip = Membership::with("member")->findOrFail($id);

            $oldPlanId = $memberShip->plan_id;
            $memberShip->update(['plan_id' => $validated['plan_id']]);

            $payment = Payment::where('plan_id', $oldPlanId)
                ->orderBy('id', 'desc')
                ->first();

            if ($payment) {
                $payment->update([
                    'total'   => $validated['total'],
                    'balance' => $validated['total'] - $payment->paid_amount,
                    'plan_id' => $validated['plan_id'],
                ]);
            }

            DB::commit();

            SendWhatsappMessageJob::dispatch($memberShip->member->whatsapp_number, Messages::TEMPLATES[Messages::MEMBERSHIP_UPDATED]);

            return $this->response('Membership plan and payment successfully updated.', null, true);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response($e->getMessage(), null, false);
        }
    }

    public function destroy(Member $member)
    {
        try {
            $member->delete();

            return response()->json(['message' => 'Member successfully deleted.', 'status' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function uploadProfilePicture($file)
    {
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('media/member/profile_picture/'), $fileName);
        return $fileName;
    }
    private function attachMembershipPlan(Member $member, $payload)
    {
        $member->memberships()->create([
            'plan_id'         => $payload->get('plan_id'),
            'plan_start_date' => $payload->get('plan_start_date'),
            'plan_end_date'   => $payload->get('plan_end_date'),
        ]);
    }
    private function recordPayment(Member $member, $payload, $request)
    {
        $planPrice     = $request->plan_price;
        $discount      = $payload->get('discount', 0);
        $paidAmount    = $payload->get('paid_amount', 0);
        $afterDiscount = $planPrice - $discount;

        $member->payments()->create([
            'plan_id'          => $payload->get('plan_id'),
            'payment_mode_id'  => $payload->get('payment_mode_id'),
            'payment_mode_ref' => $payload->get('payment_mode_ref'),
            'paid_amount'      => $paidAmount,
            'discount'         => $discount,
            'balance'          => $afterDiscount - $paidAmount,
            'after_discount'   => $afterDiscount,
            'total'            => $planPrice,
            'description'      => "Payment for plan for {$request->plan_name}",
        ]);
    }

    public function membershipRenewal(MembershipRenewRequest $request, Membership $memberShip)
    {
        $validated = $request->validated();

        DB::beginTransaction();

        $memberShip->load('member');

        try {
            // Expire the current memberShip
            $memberShip->update(['status' => Membership::STATUS_EXPIRED]);

            // Check for duplicate membership
            $duplicate = Membership::where('member_id', $validated['member_id'])
                ->where('plan_id', $validated['plan_id'])
                ->whereDate('plan_start_date', $validated['plan_start_date'])
                ->whereDate('plan_end_date', $validated['plan_end_date'])
                ->exists();

            if ($duplicate) {
                return $this->response('Duplicate membership found for the same plan and period.', null, false);
            }

            // Create new membership
            $newMemberShip = Membership::create($validated);

            $previousTotal = ($validated['plan_price'] ?? 0) + ($validated['balance'] ?? 0) ?? 0;

            // Attach payment to the new membership
            $newMemberShip->payments()->create([
                'plan_id'          => $validated['plan_id'],
                'payment_mode_id'  => $validated['payment_mode_id'],
                'payment_mode_ref' => $validated['payment_mode_ref'],
                'paid_amount'      => $validated['paid_amount'],
                'discount'         => $validated['discount'] < $previousTotal ? $validated['discount'] : 0,
                'balance'          => abs($previousTotal - $validated['paid_amount']),
                'after_discount'   => $validated['paid_amount'],
                'total'            => $previousTotal,
                'description'      => "Payment for Membership renewal",
                'member_id'        => $validated['member_id'],
            ]);

            DB::commit();

            SendWhatsappMessageJob::dispatch($memberShip->member->whatsapp_number, Messages::TEMPLATES[Messages::MEMBERSHIP_RENEWED]);

            return $this->response('Membership renewed successfully.', null, true);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response($e->getMessage(), null, true);
        }
    }

}
