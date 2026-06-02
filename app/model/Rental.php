<?php


class Rental extends Model
{
    protected $table = 'rentals';


    public function createRental($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = 'pending';
        return $this->create($data);
    }


    public function getUserRentals($userId)
    {
        $this->db->query("
            SELECT rentals.*, cars.brand, cars.model, cars.image,
                   users.name as owner_name, users.phone as owner_phone
            FROM rentals
            JOIN cars ON rentals.car_id = cars.id
            JOIN users ON cars.user_id = users.id
            WHERE rentals.user_id = :user_id
            ORDER BY rentals.created_at DESC
        ");
        $this->db->bind(':user_id', $userId);
        return $this->db->fetchAll();
    }


    public function getOwnerRentals($ownerId)
    {
        $this->db->query("
            SELECT rentals.*, cars.brand, cars.model,
                   users.name as renter_name, users.phone as renter_phone
            FROM rentals
            JOIN cars ON rentals.car_id = cars.id
            JOIN users ON rentals.user_id = users.id
            WHERE cars.user_id = :owner_id
            ORDER BY rentals.created_at DESC
        ");
        $this->db->bind(':owner_id', $ownerId);
        return $this->db->fetchAll();
    }

    // changing status of rent
    public function updateStatus($rentalId, $status)
    {
        return $this->update($rentalId, ['status' => $status]);
    }

    // checking avaliablity of car
    public function isCarAvailable($carId, $startDate, $endDate)
    {
        $this->db->query("
            SELECT COUNT(*) as count FROM rentals
            WHERE car_id = :car_id
            AND status IN ('pending', 'confirmed')
            AND (
                (start_date <= :start_date AND end_date >= :start_date)
                OR (start_date <= :end_date AND end_date >= :end_date)
                OR (start_date >= :start_date AND end_date <= :end_date)
            )
        ");
        $this->db->bind(':car_id', $carId);
        $this->db->bind(':start_date', $startDate);
        $this->db->bind(':end_date', $endDate);
        $result = $this->db->fetch();
        return $result['count'] == 0;
    }
}
