<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * PluginSendMessageData
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    opMessagePlugin
 * @subpackage form
 */
abstract class PluginSendMessageData extends BaseSendMessageData
{
  const MESSAGE_TYPE_RECEIVE = 'receive';
  const MESSAGE_TYPE_SEND = 'send';
  const MESSAGE_TYPE_DRAFT = 'draft';
  const MESSAGE_TYPE_DUST = 'dust';

  const SMARTPHONE_SUBJECT = '%%%SMARTPHONE_SUBJECT%%%';

  protected
    $previous = null,
    $next = null;

  /**
   * メッセージが本人送信のものかどうか確認する
   * @param  $member_id
   * @return boolean
   */
  public function getIsSender($memberId = null)
  { 
    if (is_null($memberId))
    {
      $memberId = sfContext::getInstance()->getUser()->getMemberId();
    }
    if ($this->getMemberId() == $memberId)
    {

      return 1;
    }
    else
    {

      return 0;
    }
  }

  /**
   * メッセージが本人宛で且つ送信済みかどうか確認する
   * @param  $member_id
   * @return int
   */
  public function getIsReceiver($memberId)
  { 
    $message = Doctrine::getTable('MessageSendList')->getMessageByReferences($memberId, $this->getId());
    if ($message && $this->getIsSend())
    {

      return 1;
    }
    else
    {

      return 0;
    }
  }

  /**
   * 宛先リストを取得する
   * @return array
   */
  public function getSendList()
  {
    $objs = Doctrine::getTable('MessageSendList')->getMessageSendList($this->getId());

    return $objs;
  }

  /**
   * 宛先(1件)を取得する
   * @return Member
   */
  public function getSendTo()
  {
    $objs = $this->getSendList();
    if (0 == count($objs))
    {

      return null;
    }

    return $objs[0]->getMember();
  }

  /**
   * 添付ファイルを取得する（idの昇順）
   * @return array
   */
  public function getMessageFiles(Doctrine_Query $q = null)
  {
    if (is_null($q))
    {
      $a = Doctrine::getTable('MessageFile')
        ->orderBy('id ASC');
    }
    $files = parent::getMessageFile($q);

    return $files;
  }

  public function getMessageSendLists()
  {
    return Doctrine::getTable('MessageSendList')->findByMessageId($this->getId());
  }

  public function getPrevious($type = self::MESSAGE_TYPE_RECEIVE, $myMemberId = null)
  {
    if (is_null($this->previous))
    {
      switch ($type)
      {
        case self::MESSAGE_TYPE_RECEIVE:
          $this->previous = Doctrine::getTable('MessageSendList')->getPreviousSendMessageData($this, $myMemberId);
          break;
        case self::MESSAGE_TYPE_SEND:
          $this->previous = Doctrine::getTable('SendMessageData')->getPreviousSendMessageData($this, $myMemberId);
          break;
        case self::MESSAGE_TYPE_DUST:
          $this->previous = Doctrine::getTable('DeletedMessage')->getPreviousSendMessageData($this, $myMemberId);
          break;
        default:
          throw new LogicException(sprintf('The specified message type "%" is not supported here.', $type));
      }
    }

    return $this->previous;
  }

  public function getNext($type = self::MESSAGE_TYPE_RECEIVE, $myMemberId = null)
  {
    if (is_null($this->next))
    {
      switch ($type)
      {
        case self::MESSAGE_TYPE_RECEIVE:
          $this->next = Doctrine::getTable('MessageSendList')->getNextSendMessageData($this, $myMemberId);
          break;
        case self::MESSAGE_TYPE_SEND:
          $this->next = Doctrine::getTable('SendMessageData')->getNextSendMessageData($this, $myMemberId);
          break;
        case self::MESSAGE_TYPE_DUST:
          $this->next = Doctrine::getTable('DeletedMessage')->getNextSendMessageData($this, $myMemberId);
          break;
        default:
          throw new LogicException(sprintf('The specified message type "%" is not supported here.', $type));
      }
    }

    return $this->next;
  }

  public function getDecoratedMessageBody()
  {
    $type = $this->getMessageType()->type_name;
    if ('message' === $type)
    {

      return $this->body;
    }

    $methodName = 'decorate'.sfInflector::camelize($type).'Body';

    // For calling magic method, must create instance. It is limitation of PHP 5.2.x
    $instance = new opMessagePluginFormatter();

    return $instance->$methodName($this);
  }

  public function preUpdate($event)
  {
    if (in_array('is_send', $this->_modified) && 1 == $this->_data['is_send'])
    {
      Doctrine_Query::create()
        ->update('MessageSendList m')
        ->set('m.created_at', '?', date('Y-m-d H:i:s'))
        ->where('m.message_id = ?', $this->id)
        ->execute();
    }
  }

  public function getSubject()
  {
    $subject = $this->_get('subject');
    if ($subject === self::SMARTPHONE_SUBJECT)
    {
      return sfContext::getInstance()->getI18n()->__('Message from smartphone');
    }

    return $subject;
  }
}
