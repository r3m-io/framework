<?php

namespace Event\R3m\Io\Framework;

use R3m\Io\App;
use R3m\Io\Config;

use R3m\Io\Module\Controller;
use R3m\Io\Module\Core;
use R3m\Io\Module\Database;
use R3m\Io\Module\Dir;
use R3m\Io\Module\File;
use R3m\Io\Module\Parse;
use R3m\Io\Module\Parse\Token;
use R3m\Io\Module\Stream\Notification;

use Exception;

use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

use R3m\Io\Exception\FileWriteException;
use R3m\Io\Exception\ObjectException;

class Email {

    /**
     * @throws ObjectException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws FileWriteException
     * @throws \Doctrine\DBAL\Exception
     * @throws \Doctrine\ORM\ORMException
     * @throws Exception
     */
    public static function queue(App $object, $action='', $options=[]): void
    {
        $object_mail = App::instance();
        Controller::configure(
            $object_mail,
            \Host\Api\Workandtravel\World\Controller\System::class,
        );
        $read = $object->data_read($object_mail->config('project.dir.data') . 'App' . $object_mail->config('ds') . 'Email' . $object_mail->config('extension.json'));
        $is_email = false;
        $email = false;
        if ($read) {
            foreach ($read->get('email') as $email) {
                if (
                    property_exists($email, 'action') &&
                    $email->action === $action
                ) {
                    $is_email = true;
                    break;
                }
            }
        }
        if (!$is_email) {
            if($object_mail->config('project.log.mail')){
                $object_mail->logger($object_mail->config('project.log.mail'))->error('Email not found: ' . $action);
            }
            return;
        }
        $to = false;
        $replyTo = false;
        $cc = false;
        $bcc = false;
        $subject = false;
        $text = false;
        $body = false;
        $priority = 1;
        $attachments = [];
        $parse = new Parse($object_mail);
        if (property_exists($email, 'to')) {
            $parameters = [];
            $parameters[] = $email->to;
            $parameters = Config::parameters($object_mail, $parameters);
            $to = reset($parameters);
        }
        if (property_exists($email, 'replyTo')) {
            $parameters = [];
            $parameters[] = $email->replyTo;
            $parameters = Config::parameters($object_mail, $parameters);
            $replyTo = reset($parameters);
        }
        if (property_exists($email, 'cc')) {
            $parameters = [];
            $parameters[] = $email->cc;
            $parameters = Config::parameters($object_mail, $parameters);
            $cc = reset($parameters);
        }
        if (property_exists($email, 'bcc')) {
            $parameters = [];
            $parameters[] = $email->bcc;
            $parameters = Config::parameters($object_mail, $parameters);
            $bcc = reset($parameters);
        }
        if (property_exists($email, 'subject')) {
            $parameters = [];
            $parameters[] = $email->subject;
            $parameters = Config::parameters($object_mail, $parameters);
            $subject = reset($parameters);
        }
        if (property_exists($email, 'priority')) {
            $parameters = [];
            $parameters[] = $email->priority;
            $parameters = Config::parameters($object_mail, $parameters);
            $priority = reset($parameters);
            if (
                empty($priority) ||
                !is_numeric($priority)
            ) {
                $priority = 1;
            }
        }
        if (property_exists($email, 'attachment')) {
            $attachments = $email->attachment;
        }
        if (property_exists($email, 'text')) {
            if (
                is_object($email->text) &&
                property_exists($email->text, 'url')
            ) {
                $parameters = [];
                $parameters[] = $email->text->url;
                $parameters = Config::parameters($object_mail, $parameters);
                $text_url = reset($parameters);
                if (File::exist($text_url)) {
                    $text = File::read($text_url);
                } else {
                    throw new Exception('Email text file not found: ' . $text_url);
                }
                $object_mail->set('options', $options);
                $object_mail->set('r3m.io.parse.view.source.url', $text_url);
                $text = $parse->compile($text, $object_mail->data(), $object_mail);
                Parse::readback($object_mail, $parse, App::SCRIPT);
                Parse::readback($object_mail, $parse, App::LINK);
                if ($object_mail->has('subject')) {
                    $subject = $object_mail->get('subject');
                }
                if ($object_mail->has('to')) {
                    if ($to === false) {
                        $to = (array) $object_mail->get('to');
                    } else {
                        $to = array_merge($to, $object_mail->get('to'));
                    }
                }
                if ($object_mail->has('cc')) {
                    if ($cc === false) {
                        $cc = (array) $object_mail->get('cc');
                    } else {
                        $cc = array_merge($cc, $object_mail->get('cc'));
                    }
                }
                if ($object_mail->has('bcc')) {
                    if ($bcc === false) {
                        $bcc = (array) $object_mail->get('bcc');
                    } else {
                        $bcc = array_merge($bcc, $object_mail->get('bcc'));
                    }
                }
                if ($object_mail->has('replyTo')) {
                    if ($replyTo === false) {
                        $replyTo = (array) $object_mail->get('replyTo');
                    } else {
                        $replyTo = array_merge($replyTo, $object_mail->get('replyTo'));
                    }
                }
                if ($object_mail->has('priority')) {
                    $priority = $object_mail->get('priority');
                    if (
                        empty($priority) ||
                        !is_numeric($priority)
                    ) {
                        $priority = 1;
                    }
                }
                if ($object_mail->has('attachments')) {
                    if (
                        empty($attachments) &&
                        !is_array($attachments)
                    ) {
                        $attachments = (array) $object_mail->get('attachments');
                    } else {
                        $attachments = array_merge($attachments, $object_mail->get('attachments'));
                    }
                }
            }
        }
        if (property_exists($email, 'body')) {
            if (
                is_object($email->body) &&
                property_exists($email->body, 'url')
            ) {
                $parameters = [];
                $parameters[] = $email->body->url;
                $parameters = Config::parameters($object_mail, $parameters);
                $body_url = reset($parameters);
                if (File::exist($body_url)) {
                    $body = File::read($body_url);
                } else {
                    throw new Exception('Email body file not found: ' . $body_url);
                }
                $object_mail->set('options', $options);
                $object_mail->set('r3m.io.parse.view.source.url', $body_url);
                $body = $parse->compile($body, $object_mail->data(), $object_mail);
                Parse::readback($object_mail, $parse, App::SCRIPT);
                Parse::readback($object_mail, $parse, App::LINK);
                if ($object_mail->has('subject')) {
                    $subject = $object_mail->get('subject');
                }
                if ($object_mail->has('to')) {
                    if ($to === false) {
                        $to = $object_mail->get('to');
                    } else {
                        $to = array_merge($to, $object_mail->get('to'));
                    }
                }
                if ($object_mail->has('cc')) {
                    if ($cc === false) {
                        $cc = $object_mail->get('cc');
                    } else {
                        $cc = array_merge($cc, $object_mail->get('cc'));
                    }
                }
                if ($object_mail->has('bcc')) {
                    if ($bcc === false) {
                        $bcc = $object_mail->get('bcc');
                    } else {
                        $bcc = array_merge($bcc, $object_mail->get('bcc'));
                    }
                }
                if ($object_mail->has('replyTo')) {
                    if ($replyTo === false) {
                        $replyTo = $object_mail->get('replyTo');
                    } else {
                        $replyTo = array_merge($replyTo, $object_mail->get('replyTo'));
                    }
                }
                if ($object_mail->has('priority')) {
                    $priority = $object_mail->get('priority');
                    if (
                        empty($priority) ||
                        !is_numeric($priority)
                    ) {
                        $priority = 1;
                    }
                }
                if ($object_mail->has('attachments')) {
                    if (
                        empty($attachments) ||
                        !is_array($attachments)
                    ) {
                        $attachments = $object_mail->get('attachments');
                    } else {
                        $attachments = array_merge($attachments, $object_mail->get('attachments'));
                    }
                }
            }
        }
        if (
            !empty($to) &&
            is_array($to) &&
            (
                $text ||
                $body
            )
        ) {
            $entityManager = Database::entityManager($object_mail);
            $node = new \Entity\EmailQueue();
            $node->setObject($object_mail);
            $node->setTo($to);
            if (
                !empty($replyTo) &&
                is_array($replyTo)
            ) {
                $node->setReplyTo($replyTo);
            }
            if (
                !empty($cc) &&
                is_array($cc)
            ) {
                $node->setCc($cc);
            }
            if (
                !empty($bcc) &&
                is_array($bcc)
            ) {
                $node->setBcc($bcc);
            }
            if ($subject) {
                $node->setSubject($subject);
            }
            if ($text) {
                $node->setText($text);
            }
            if ($body) {
                $node->setBody($body);
            }
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $nr => $attachment) {
                    if (File::exist($attachment) === false) {
                        unset($attachments[$nr]);
                    }
                }
                if (!empty($attachments)) {
                    $node->setAttachment(Core::object($attachments, Core::OBJECT_ARRAY));
                }
            }
            if ($priority) {
                $node->setPriority($priority);
            }
            $entityManager->persist($node);
            $entityManager->flush();
        }
    }
}