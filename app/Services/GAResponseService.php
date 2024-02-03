<?php

namespace App\Services;

use Google\Analytics\Data\V1beta\MetricType;
use Google\Analytics\Data\V1beta\RunReportResponse;use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;

class GAResponseService
{
    private $gaClient;
    private $propertyId;
    public function __construct()
    {
        $this->gaClient = new BetaAnalyticsDataClient([
            'credentials' => env('GA_SERVICE_CREDENTIAL')
        ]);
        $this->propertyId = env('GA_PROPERTY_ID');
    }
    public function fetchFromDataApi($request)
    {
        $result = [];
        $metrics = [];
        $errMessage = '';

        foreach ($request->metrics as $metric) {
            $metrics[] = new Metric([
                'name' => $metric
            ]);
        }

        $dimensions = [];
        foreach ($request->dimensions as $dimension) {
            $dimensions[] = new Dimension([
                'name' => $dimension
            ]);
        }
        $reportRequest = (new RunReportRequest())
            ->setProperty("properties/{$this->propertyId}")
            ->setMetrics($metrics)
            ->setDimensions($dimensions)
            ->setDateRanges([
                new DateRange([
                    'start_date' => $request->startDate,
                    'end_date' => $request->endDate,
                ])
            ])
        ;

        try {
            $googleResponse = $this->gaClient->runReport($reportRequest);
            $result = [
                'success' => true,
                'response' => $googleResponse
            ];
        } catch (\Throwable $th) {
            $result = [
                'success' => false,
                'message' => $th->getMessage()
            ];
        }

        return $result;
    }
    public function convertResponseToJson(RunReportResponse $runReportResponse): array
    {
        $data = [];
        foreach($runReportResponse->getDimensionHeaders() AS $key => $dimensionHeader) {
            $data['dimensionHeaders'][$key]['name'] = $dimensionHeader->getName();
        }
        $metricType = new MetricType();
        foreach($runReportResponse->getMetricHeaders() AS $key => $metricHeader) {
            $data['metricHeaders'][$key]['name'] = $metricHeader->getName();
            $data['metricHeaders'][$key]['type'] = $metricType->name($metricHeader->getType());
        }
        foreach($runReportResponse->getRows() AS $key => $row) {
            $rowValues = [];
            $dimensionValues = [];
            foreach($row->getMetricValues() AS $metricKey => $metricValue) {
                $rowValues['value'] = $metricValue->getValue();
            }
            foreach($row->getDimensionValues() AS $dimensionKey => $dimensionValue) {
                $dimensionValues['value'] = $dimensionValue->getValue();
            }
            $data['rows']['metricValues'][$key] = $rowValues;
            $data['rows']['dimensionValues'][$key] = $dimensionValues;
        }
        // totals here
        // foreach($runReportResponse->getTotals() AS $totalKey => $total) {
        //     $data['totals'][$totalKey]['']
        // }
        return $data;
    }
}