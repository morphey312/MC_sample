<?php

namespace App\V1\Contracts\Repositories\Notification;

use App\V1\Contracts\Repositories\BaseRepository;

interface MailingTemplateRepository extends BaseRepository
{
    /**
     * Get usable templates
     *
     * @param string $scenario
     * @param int|null $clinicId
     * @param array $positionIds
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsableTemplates($scenario, $clinicId, $positionIds);

    /**
     * Get usable templates by employee positions
     *
     * @param $employeePositions
     *
     * @return \Illuminate\Support\Collection
     */
    public function getUsableTemplatesByEmployeePositions($employeePositions);
}
