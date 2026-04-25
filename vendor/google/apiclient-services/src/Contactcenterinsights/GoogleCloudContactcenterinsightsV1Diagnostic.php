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

class GoogleCloudContactcenterinsightsV1Diagnostic extends \Google\Model
{
  /**
   * Output only. The complete sequence of thoughts and actions taken by the
   * agent.
   *
   * @var string
   */
  public $analysisSummary;
  /**
   * Output only. The filter used to select the conversations that were included
   * in the diagnostic.
   *
   * @var string
   */
  public $conversationFilter;
  /**
   * Output only. The time at which the diagnostic was created.
   *
   * @var string
   */
  public $createTime;
  /**
   * Immutable. Identifier. The resource name of the diagnostic.
   *
   * @var string
   */
  public $name;
  protected $reportType = GoogleCloudContactcenterinsightsV1DiagnosticReport::class;
  protected $reportDataType = '';

  /**
   * Output only. The complete sequence of thoughts and actions taken by the
   * agent.
   *
   * @param string $analysisSummary
   */
  public function setAnalysisSummary($analysisSummary)
  {
    $this->analysisSummary = $analysisSummary;
  }
  /**
   * @return string
   */
  public function getAnalysisSummary()
  {
    return $this->analysisSummary;
  }
  /**
   * Output only. The filter used to select the conversations that were included
   * in the diagnostic.
   *
   * @param string $conversationFilter
   */
  public function setConversationFilter($conversationFilter)
  {
    $this->conversationFilter = $conversationFilter;
  }
  /**
   * @return string
   */
  public function getConversationFilter()
  {
    return $this->conversationFilter;
  }
  /**
   * Output only. The time at which the diagnostic was created.
   *
   * @param string $createTime
   */
  public function setCreateTime($createTime)
  {
    $this->createTime = $createTime;
  }
  /**
   * @return string
   */
  public function getCreateTime()
  {
    return $this->createTime;
  }
  /**
   * Immutable. Identifier. The resource name of the diagnostic.
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }
  /**
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  /**
   * Output only. The report containing the findings of the diagnostic.
   *
   * @param GoogleCloudContactcenterinsightsV1DiagnosticReport $report
   */
  public function setReport(GoogleCloudContactcenterinsightsV1DiagnosticReport $report)
  {
    $this->report = $report;
  }
  /**
   * @return GoogleCloudContactcenterinsightsV1DiagnosticReport
   */
  public function getReport()
  {
    return $this->report;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContactcenterinsightsV1Diagnostic::class, 'Google_Service_Contactcenterinsights_GoogleCloudContactcenterinsightsV1Diagnostic');
