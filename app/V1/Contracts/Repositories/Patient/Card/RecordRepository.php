<?php

namespace App\V1\Contracts\Repositories\Patient\Card;

use App\V1\Contracts\Repositories\BaseRepository;

interface RecordRepository extends BaseRepository
{
    /**
     * Find record related to appointment
     * 
     * @param int $appointmentId
     * @param string $type
     * 
     * @return Record
     */ 
	public function findByAppointment($appointmentId, $type);
}