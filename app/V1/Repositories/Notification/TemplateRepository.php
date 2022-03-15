<?php

namespace App\V1\Repositories\Notification;

use App\V1\Contracts\Repositories\Notification\TemplateRepository as RepositoryInterface;
use App\V1\Repositories\BaseRepository;
use App\V1\Models\Notification\Template;
use App\V1\Contracts\Repositories\Query\Notification\TemplateFilter;
use App\V1\Contracts\Repositories\Query\Notification\TemplateSorter;

class TemplateRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return Template::query();
    }

    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter(TemplateFilter::class, $filters);
    }

    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter(TemplateSorter::class, $order);
    }

    /**
     * @inherit
     */
    public function getUsableTemplates($scenario, $clinicId, $positionIds = [])
    {
        $query = $this->query();
        $result = $this->setupQuery($query, null, null, false)
            ->with(['channel', 'parent'])
            ->where($query->qualifyColumn('scenario'), '=', $scenario)
            ->where($query->qualifyColumn('disabled'), '=', 0);

        if ($clinicId !== null) {
            $result->whereIn($query->qualifyColumn('id'), function($query) use($clinicId) {
                $query->select('notification_template_clinics.template_id')
                    ->from('notification_template_clinics')
                    ->where('notification_template_clinics.clinic_id', '=', $clinicId);
            });
        }

        if (count($positionIds) > 0) {
            $result->whereIn($query->qualifyColumn('id'), function($query) use($positionIds) {
                $query->select('notification_template_positions.template_id')
                    ->from('notification_template_positions')
                    ->whereIn('notification_template_positions.position_id', $positionIds);
            });
        }

        return $result->get();
    }

    /**
     * @inherit
     */
    public function getUsableTemplatesByEmployeePositions($employeePositions)
    {
        return $this->getUsableTemplates(
            Template::SCENARIO_TELEGRAM_CHAT_BOT_NOTIFICATION,
            null,
            $employeePositions
        );
    }

      /**
     * @inherit
     */
    public function getTemplateByScenario($scenario)
    {
        $query = $this->query();
        return $query->where($query->qualifyColumn('scenario'), '=' , $scenario)->first();
    }
}
