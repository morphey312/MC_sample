<?php

namespace App\V1\Traits\Elastic\Report;

trait IndexName
{
    /**
     * Base config path
     *
     * @var string
     */
    protected $basePath = 'services.elasticsearch.indices';

    /**
     * Income report appointments index
     *
     * @var string
     */
    protected $reportAppointmentsIncomeIndexName = 'income_appointments';

    /**
     * Income report payments index
     *
     * @var string
     */
    protected $reportPaymentsIndexName = 'income_payments';

    /**
     * Redirects report external index
     *
     * @var string
     */
    protected $reportRedirestsExternalIndexName = 'redirects_external';

    /**
     * Redirects report external v3 index
     *
     * @var string
     */
    protected $reportRedirestsExternalIndexNameV3 = 'redirects_external_v3';

    /**
     * Redirects report internal index
     *
     * @var string
     */
    protected $reportRedirectsInternalIndexName = 'redirects_internal';

    /**
     * Redirects report internal v3 index
     *
     * @var string
     */
    protected $reportRedirectsInternalIndexNameV3 = 'redirects_internal_v3';

    /**
     * Call center calls and appointments index name
     *
     * @var string
     */
    protected $reportCallCenterSlicesIndexName = 'call_center_slices';

    /**
     * Call center calls and appointments index name
     *
     * @var string
     */
    protected $reportCallCenterBonuesIndexName = 'call_center_bonuses';

    /**
     * Call center session logs index name
     *
     * @var string
     */
    protected $reportCallCenterSessionLogIndexName = 'call_center_session_logs';

    /**
     * Get config index name
     *
     * @param string $index
     *
     * @return string
     */
    protected function getElasticIndexName($index = null)
    {
        return $index ? config($this->basePath . '.' . $index) : '';
    }

    /**
     * Get income report appointment index name
     *
     * @return string
     */
    public function incomeAppointmentIndexName()
    {
        return $this->getElasticIndexName($this->reportAppointmentsIncomeIndexName);
    }

    /**
     * Get income report payment index name
     *
     * @return string
     */
    public function incomePaymentIndexName()
    {
        return $this->getElasticIndexName($this->reportPaymentsIndexName);
    }

    /**
     * Get external redirects report index name
     *
     * @return string
     */
    public function redirectsExternalIndexName()
    {
        return $this->getElasticIndexName($this->reportRedirestsExternalIndexName);
    }

    /**
     * Get external redirects report v3 index name
     *
     * @return string
     */
    public function redirectsExternalIndexNameV3()
    {
        return $this->getElasticIndexName($this->reportRedirestsExternalIndexNameV3);
    }

    /**
     * Get internal redirects report index name
     *
     * @return string
     */
    public function redirectsInternalIndexName()
    {
        return $this->getElasticIndexName($this->reportRedirectsInternalIndexName);
    }

    /**
     * Get internal redirects report v3 index name
     *
     * @return string
     */
    public function redirectsInternalIndexNameV3()
    {
        return $this->getElasticIndexName($this->reportRedirectsInternalIndexNameV3);
    }

    /**
     * Get call center reports call index name
     *
     * @return string
     */
    public function callCenterSlicesIndexName()
    {
        return $this->getElasticIndexName($this->reportCallCenterSlicesIndexName);
    }

    /**
     * Get call center operator bonuses report index name
     *
     * @return string
     */
    public function callCenterBonusesIndexName()
    {
        return $this->getElasticIndexName($this->reportCallCenterBonuesIndexName);
    }

    /**
     * Get call center session logs index name
     *
     * @return string
     */
    public function callCenterSessionLogIndexName()
    {
        return $this->getElasticIndexName($this->reportCallCenterSessionLogIndexName);
    }
}
