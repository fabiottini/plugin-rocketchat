<?php

namespace Kanboard\Plugin\RocketChat\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;
use Kanboard\Model\TaskModel;

/**
 * RocketChat Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class RocketChat extends Base implements NotificationInterface
{
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
        $webhook = $this->userMetadataModel->get($user['id'], 'rocketchat_webhook_url', $this->configModel->get('rocketchat_webhook_url'));
        $channel = $this->userMetadataModel->get($user['id'], 'rocketchat_webhook_channel');

        if (! empty($webhook)) {
            if ($eventName === TaskModel::EVENT_OVERDUE) {
                foreach ($eventData['tasks'] as $task) {
                    $project = $this->projectModel->getById($task['project_id']);
                    $eventData['task'] = $task;
                    $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
                }
            } else {
                $project = $this->projectModel->getById($eventData['task']['project_id']);
                $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
            }
        }
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyProject(array $project, $eventName, array $eventData)
    {
        $webhook = $this->projectMetadataModel->get($project['id'], 'rocketchat_webhook_url');
        $channel = $this->projectMetadataModel->get($project['id'], 'rocketchat_webhook_channel');

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $channel, $project, $eventName, $eventData);
        }
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     * @return array
     */
    public function getMessage(array $project, $eventName, array $eventData)
    {
        $title = '['.$project['name'].'] #'.$eventData['task']['id'].' '.$eventData['task']['title'];
        
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
            $message = $this->notificationModel->getTitleWithAuthor($author, $eventName, $eventData);
        } else {
            $message = $this->notificationModel->getTitleWithoutAuthor($eventName, $eventData);
        }
        if ($this->configModel->get('application_url') !== '') {
            $url = $this->helper->url->to('TaskViewController', 'show', array('task_id' => $eventData['task']['id'], 'project_id' => $project['id']), '', true);
            $message = preg_replace('/(?:#|n°)(\d+)( |$)/', '[#$1]('.$url.')$2', $message);
        }

        // https://rocket.chat/docs/developer-guides/rest-api/chat/postmessage/#attachments-detail
        $additionalContents = array();
        if (isset($eventData['comment']) && $eventName != 'comment.delete') {
            $additionalContents[] = array("value" => $eventData['comment']['comment']);
        }
        else if (isset($eventData['subtask'])) {
            $additionalContents[] = array("value" => "[".$eventData['subtask']['status_name']."] ".$eventData['subtask']['title']);
        }
        else if (isset($eventData['task'])
                  && $eventName != 'task.move.column'
                  && $eventName != 'task.move.position'
                  && $eventName != 'task.close'
                  && $eventName != 'task_internal_link.create_update'
                  && $eventName != 'task_internal_link.delete'
                  && $eventName != 'task.file.create'
                  && $eventName != 'comment.delete') {
            if (isset($eventData['task']['assignee_username'])) {
                $additionalContents[] = array("title" => t('Assignee:'), "value" => $eventData['task']['assignee_username']);
            }
            if (isset($eventData['task']['date_started']) && 0 != $eventData['task']['date_started']) {
                $additionalContents[] = array("title" => t('Started:'), "value" => date('Y-m-d', $eventData['task']['date_started']));
            }
            if (isset($eventData['task']['date_due']) && 0 != $eventData['task']['date_due']) {
                $additionalContents[] = array("title" => t('Due date:'), "value" => date('Y-m-d', $eventData['task']['date_due']));
            }
            if (isset($eventData['task']['description']) && !empty($eventData['task']['description'])) {
                $additionalContents[] = array("title" => t('Description'), "value" => $eventData['task']['description']);
            }
        }

        return array(
            'username' => 'Kanboard',
            'icon_url' => 'https://kanboard.org/assets/img/favicon.png',
            'attachments' => array(
                    array(
                        'title' => $title,
                        'text' => $message,
                        'fields' => $additionalContents,
                        'color' => $eventData['task']['color_id']
                    )
            )
        );
    }

    /**
     * Send message to RocketChat
     *
     * @access private
     * @param  string    $webhook
     * @param  string    $channel
     * @param  array     $project
     * @param  string    $eventName
     * @param  array     $eventData
     */
    protected function sendMessage($webhook, $channel, array $project, $eventName, array $eventData)
    {
        $payload = $this->getMessage($project, $eventName, $eventData);
        if (! empty($channel)) {
          $payload['channel'] = $channel;
        }

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}
