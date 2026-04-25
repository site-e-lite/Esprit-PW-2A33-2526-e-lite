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

class GoogleCloudContactcenterinsightsV1SherlockStep extends \Google\Collection
{
  protected $collection_key = 'toolCalls';
  /**
   * Output only. Natural language input stimulus.
   *
   * @var string[]
   */
  public $textInput;
  /**
   * Output only. The reasoning or internal monologue of the agent.
   *
   * @var string
   */
  public $thought;
  protected $toolCallsType = GoogleCloudContactcenterinsightsV1ToolCall::class;
  protected $toolCallsDataType = 'array';
  /**
   * Output only. The output response from the tool execution.
   *
   * @var array[]
   */
  public $toolOutput;

  /**
   * Output only. Natural language input stimulus.
   *
   * @param string[] $textInput
   */
  public function setTextInput($textInput)
  {
    $this->textInput = $textInput;
  }
  /**
   * @return string[]
   */
  public function getTextInput()
  {
    return $this->textInput;
  }
  /**
   * Output only. The reasoning or internal monologue of the agent.
   *
   * @param string $thought
   */
  public function setThought($thought)
  {
    $this->thought = $thought;
  }
  /**
   * @return string
   */
  public function getThought()
  {
    return $this->thought;
  }
  /**
   * Output only. The tool call issued by the agent.
   *
   * @param GoogleCloudContactcenterinsightsV1ToolCall[] $toolCalls
   */
  public function setToolCalls($toolCalls)
  {
    $this->toolCalls = $toolCalls;
  }
  /**
   * @return GoogleCloudContactcenterinsightsV1ToolCall[]
   */
  public function getToolCalls()
  {
    return $this->toolCalls;
  }
  /**
   * Output only. The output response from the tool execution.
   *
   * @param array[] $toolOutput
   */
  public function setToolOutput($toolOutput)
  {
    $this->toolOutput = $toolOutput;
  }
  /**
   * @return array[]
   */
  public function getToolOutput()
  {
    return $this->toolOutput;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleCloudContactcenterinsightsV1SherlockStep::class, 'Google_Service_Contactcenterinsights_GoogleCloudContactcenterinsightsV1SherlockStep');
