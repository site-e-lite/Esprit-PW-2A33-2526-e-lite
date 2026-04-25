<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\Contactcenterinsights;

class GoogleCloudContactcenterinsightsV1alpha1DiagnosticReport extends \Google\Collection
{
  protected $collection_key = 'lossPatterns';
  protected $intentStatsType = GoogleCloudContactcenterinsightsV1alpha1DiagnosticReportIntentStats::class;
  protected $intentStatsDataType = 'array';
  protected $lossPatternsType = GoogleCloudContactcenterinsightsV1alpha1LossPattern::class;
  protected $lossPatternsDataType = 'array';
  protected $metricsType = GoogleCloudContactcenterinsightsV1alpha1MetricValue::class;
  protected $metricsDataType = 'map';

  /**
   * Output only. A breakdown of metrics grouped by intent.
   *
   * @param GoogleCloudContactcenterinsightsV1alpha1DiagnosticReportIntentStats[] $intentStats
   */
  public function setIntentStats($intentStats)
  {
    $this->intentStats = $intentStats;
  }
  /**
   * @return GoogleCloudContactcenterinsightsV1alpha1DiagnosticReportIntentStats[]
   */
  public function getIntentStats()
  {
    return $this->intentStats;
  }
  /**
   * Output only. A list of loss patterns identified for the entire
   * project/dataset.
   *
   * @param GoogleCloudContactcenterinsightsV1alpha1LossPattern[] $lossPatterns
   */
  public function setLossPatterns($lossPatterns)
  {
    $this->lossPatterns = $lossPatterns;
  }
  /**
   * @return GoogleCloudContactcenterinsightsV1alpha1LossPattern[]
   */
  public function getLossPatterns()
  {
    return $this->lossPatterns;
  }
  /**
   * Output only. Deprecated: The type of the metric. Metrics for Outcome Based
   * Insights derived from QueryMetrics.
   *
   * @deprecated
   * @param GoogleCloudContactcenterinsightsV1alpha1MetricValue[] $metrics
   */
  public function setMetrics($metrics)
  {
    $this->metrics = $metrics;
  }
  /**
   * @deprecated
   * @return GoogleCloudContactcenterinsightsV1alpha1MetricValue[]
   */
  public function getMetrics()
  {
    return $this->metrics;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContactcenterinsightsV1alpha1DiagnosticReport::class, 'Google_Service_Contactcenterinsights_GoogleCloudContactcenterinsightsV1alpha1DiagnosticReport');
