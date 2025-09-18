<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'system_user_id',
        'first_name',
        'last_name',
        'phone_number',
        'whatsapp_number',
        'date_of_birth',
        'profile_picture',
    ];

    protected $casts = [
        'created_at' => 'datetime:d-M-y',
    ];

    protected $appends = [
        'profile_picture_raw',
        'name_with_user_id',
        'full_name',
        'profile_picture_base64',
        'last_membership',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function last_payment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getLastMemberShipAttribute()
    {
        return $this->memberships()
                                          // ->where('status', Membership::STATUS_last)
            ->with(['plan', 'payments.mode']) // Eager load the nested relationship
            ->latest()                        // Optional: gets the most recent last plan
            ->first();
    }

    public function getProfilePictureAttribute($value)
    {
        if (! $value) {
            return null;
        }

        return asset('media/member/profile_picture/' . $value);
        // return asset(env('BUCKET_URL') . '/' . $value);

    }

    public function getProfilePictureBase64Attribute()
    {
        return null;
        if ($this->profile_picture) {
            $imageData = file_get_contents($this->profile_picture);

            $md5string = base64_encode($imageData);

            return "data:image/png;base64,$md5string";
        }

        return null;
    }

    public function getProfilePictureRawAttribute()
    {
        // Ensure profile_picture exists and is not empty
        if (empty($this->profile_picture)) {
            return ''; // Return an empty string if profile_picture is not set
        }

        // Split the path string
        $arr = explode('media/member/profile_picture/', $this->profile_picture);

        // Return the part after 'media/member/profile_picture/' or an empty string if not found
        return isset($arr[1]) ? $arr[1] : '';
    }

    public function getCreatedAtAttribute($value): string
    {
        return date('d M Y', strtotime($value));
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getNameWithUserIDAttribute()
    {
        return $this->first_name . ' ' . $this->last_name . " - " . $this->system_user_id;
    }

    public function filterV1()
    {
        $model = self::query();

        $model->with("payments.mode", "last_payment.mode", "invoices")
            ->withSum("invoices", "paid_amount")
            ->withSum("payments", "paid_amount")
            ->withSum("invoices", "balance")
            ->withSum("payments", "balance")
            ->when(request()->filled('search'), function ($q) {
                $q->where(function ($q) {
                    $searchTerm = request("search") . "%";
                    $q->where('system_user_id', env('WILD_CARD') ?? 'ILIKE', $searchTerm)
                        ->orWhere('first_name', env('WILD_CARD') ?? 'ILIKE', $searchTerm)
                        ->orWhere('last_name', env('WILD_CARD') ?? 'ILIKE', $searchTerm)
                        ->orWhere('phone_number', env('WILD_CARD') ?? 'ILIKE', $searchTerm);
                });
            });

        return $model;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($member) {
            $member->invoices()->delete();
        });
    }
}
