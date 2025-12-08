<?php

namespace App\Services;

use App\Repositories\AddressRepository;
use App\Repositories\UserRepository;

class CheckoutService
{
    private AddressRepository $addrRepo;
    private UserRepository $userRepo;

    public function __construct()
    {
        $this->addrRepo = new AddressRepository();
        $this->userRepo = new UserRepository();
    }

    public function getCheckoutData(int $userId): array
    {
        // Logic from original: Join User + Address
        // The original script returns specific fields.
        $data = $this->userRepo->getUserWithAddress($userId);
        
        // Populate session if needed?
        // Original implementation populated $_SESSION with these values on GET.
        // That's a bit rigid, but if the frontend relies on session persistence of this data...
        // Let's replicate it in Controller if requested, but Service should just return data.
        
        return $data ?: [];
    }

    public function updateAddress(int $userId, array $input): void
    {
        $street   = trim($input['street_address'] ?? '');
        $barangay = trim($input['barangay'] ?? '');
        $city     = trim($input['city'] ?? '');
        $region   = trim($input['region'] ?? '');
        $province = trim($input['province'] ?? '');
        $note     = trim($input['note_to_rider'] ?? '');

        $data = [
            'street_address' => $street,
            'barangay' => $barangay,
            'city' => $city,
            'region' => $region,
            'province' => $province,
            'note_to_rider' => $note
        ];

        // Attempt update
        $updated = $this->addrRepo->updateAddress($userId, $data);

        // If not updated (likely no row existed), create one
        // Note: rowCount() returns 0 if data is same OR if row doesn't exist.
        // We should check if address exists first to be sure, or use INSERT ON DUPLICATE specific query in Repo.
        // Current Repo implementation separates Update and Insert.
        // Let's check existence first.
        $exists = $this->addrRepo->getAddressByUserId($userId);
        if ($exists) {
            // Already ran update. If 0 rows affected, it meant data was same, which is fine.
            $this->addrRepo->updateAddress($userId, $data);
        } else {
            $this->addrRepo->createAddress($userId, $data);
        }
    }

    public function updatePhoneNumber(int $userId, string $phone): void
    {
        $phone = trim($phone);
        $this->userRepo->updatePhoneNumber($userId, $phone);
    }
}
