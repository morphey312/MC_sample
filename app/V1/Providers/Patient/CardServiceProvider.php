<?php

namespace App\V1\Providers\Patient;

use App\V1\Models\Patient\Card\Document;
use App\V1\Observers\Audit\Patient\Card\DocumentAudit;
use Illuminate\Support\ServiceProvider;
use App\V1\Models\Patient\Card\Specialization;
use App\V1\Observers\Audit\Patient\Card\SpecializationAudit;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services
     *
     * @return  void
     */
    public function boot()
    {
        $this->loadRoutesFrom(base_path('routes/modules/v1/patient/card.php'));

        Specialization::observe(SpecializationAudit::class);
        Document::observe(DocumentAudit::class);
    }

    /**
     * Register services
     *
     * @return  void
     */
    public function register()
    {
        #region Record
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\RecordRepository',
            'App\V1\Repositories\Patient\Card\RecordRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\RecordFilter',
            'App\V1\Repositories\Query\Patient\Card\RecordFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\RecordSorter',
            'App\V1\Repositories\Query\Patient\Card\RecordSorter'
        );
        #endregion Record

        #region Specialization
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\SpecializationRepository',
            'App\V1\Repositories\Patient\Card\SpecializationRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\SpecializationFilter',
            'App\V1\Repositories\Query\Patient\Card\SpecializationFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\SpecializationSorter',
            'App\V1\Repositories\Query\Patient\Card\SpecializationSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\SpecializationScopes',
            'App\V1\Repositories\Query\Patient\Card\SpecializationScopes'
        );
        #endregion Specialization

        #region ConsultationRecord
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\ConsultationRecordRepository',
            'App\V1\Repositories\Patient\Card\ConsultationRecordRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ConsultationRecordFilter',
            'App\V1\Repositories\Query\Patient\Card\ConsultationRecordFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ConsultationRecordSorter',
            'App\V1\Repositories\Query\Patient\Card\ConsultationRecordSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ConsultationRecordScopes',
            'App\V1\Repositories\Query\Patient\Card\ConsultationRecordScopes'
        );
        #endregion ConsultationRecord

        #region Documents
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\DocumentRepository',
            'App\V1\Repositories\Patient\Card\DocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\DocumentFilter',
            'App\V1\Repositories\Query\Patient\Card\DocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\DocumentSorter',
            'App\V1\Repositories\Query\Patient\Card\DocumentSorter'
        );
        #endregion Documents

        #region NextVisit
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\NextVisitRepository',
            'App\V1\Repositories\Patient\Card\NextVisitRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\NextVisitFilter',
            'App\V1\Repositories\Query\Patient\Card\NextVisitFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\NextVisitSorter',
            'App\V1\Repositories\Query\Patient\Card\NextVisitSorter'
        );
        #endregion Nextvisit

        #region ArchivedCard
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\ArchivedCardRepository',
            'App\V1\Repositories\Patient\Card\ArchivedCardRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ArchivedCardFilter',
            'App\V1\Repositories\Query\Patient\Card\ArchivedCardFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ArchivedCardSorter',
            'App\V1\Repositories\Query\Patient\Card\ArchivedCardSorter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\ArchivedCardScopes',
            'App\V1\Repositories\Query\Patient\Card\ArchivedCardScopes'
        );
        #endregion ArchivedCard

        #region PrintedDocument
        $this->app->bind(
            'App\V1\Contracts\Repositories\Patient\Card\PrintedDocumentRepository',
            'App\V1\Repositories\Patient\Card\PrintedDocumentRepository'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\PrintedDocumentFilter',
            'App\V1\Repositories\Query\Patient\Card\PrintedDocumentFilter'
        );

        $this->app->bind(
            'App\V1\Contracts\Repositories\Query\Patient\Card\PrintedDocumentSorter',
            'App\V1\Repositories\Query\Patient\Card\PrintedDocumentSorter'
        );

        #endregion PrintedDocument
    }
}
