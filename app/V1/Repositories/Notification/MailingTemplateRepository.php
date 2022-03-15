<?php

namespace App\V1\Repositories\Notification;

use App\V1\Contracts\Repositories\Notification\MailingTemplateRepository as RepositoryInterface;
use App\V1\Models\Notification\MailingTemplate;
use App\V1\Repositories\BaseRepository;
use App\V1\Contracts\Repositories\Query\Notification\MailingTemplateFilter;
use App\V1\Contracts\Repositories\Query\Notification\MailingTemplateSorter;

class MailingTemplateRepository extends BaseRepository implements RepositoryInterface
{
    /**
     * @inherit
     */
    protected function query()
    {
        return MailingTemplate::query();
    }

    /**
     * @inherit
     */
    public function filter(array $filters = [])
    {
        return $this->makeFilter(MailingTemplateFilter::class, $filters);
    }

    /**
     * @inherit
     */
    public function sorter(array $order = [])
    {
        return $this->makeSorter(MailingTemplateSorter::class, $order);
    }

    /**
     * @inherit
     */
    public function getUsableTemplates($scenario, $clinicId, $positionIds = [])
    {
        $query = $this->query();
        $result = $this->setupQuery($query, null, null, false)
            ->with(['provider'])
            ->where($query->qualifyColumn('scenario'), '=', $scenario)
            ->where($query->qualifyColumn('disabled'), '=', 0);
        if ($clinicId !== null) {
            $result->whereIn($query->qualifyColumn('id'), function($query) use($clinicId) {
                $query->select('notification_mailing_template_settings_clinics.notification_mailing_template_id')
                    ->from('notification_mailing_template_settings_clinics')
                    ->where('notification_mailing_template_settings_clinics.clinic_id', '=', $clinicId);
            });
        }

        return $result->get();
    }

    /**
     * @inherit
     */
    public function getUsableTemplatesByEmployeePositions($employeePositions)
    {
        return null;
    }

      /**
     * @inherit
     */
    public function getTemplateByScenario($scenario)
    {
        $query = $this->query();
        return $query->where($query->qualifyColumn('scenario'), '=' , $scenario)->first();
    }

    public function getScheduleTemplates ()
    {
        $query = $this->query();
        $result = $this->setupQuery($query, null, null, false)
            ->with(['provider'])
            ->where($query->qualifyColumn('schedule_mailing'), '=', MailingTemplate::SEND_BY_SCHEDULE)
            ->where($query->qualifyColumn('disabled'), '=', 0);
// todo remove after test
//        if ($clinicId !== null) {
//            $result->whereIn($query->qualifyColumn('id'), function($query) use($clinicId) {
//                $query->select('notification_mailing_template_settings_clinics.notification_mailing_template_id')
//                    ->from('notification_mailing_template_settings_clinics')
//                    ->where('notification_mailing_template_settings_clinics.clinic_id', '=', $clinicId);
//            });
//        }
//        Log::channel('esputnik_test')->info('getScheduleTemplates', [
//            '$result' => $result->get(),
//        ]);

        return $result->get();
    }
}
