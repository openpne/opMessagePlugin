<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opMessageHelper.
 *
 * @package    OpenPNE
 * @subpackage helper
 * @author     Maki TAKAHASHI <takahashi@tejimaya.com>
 */

function op_message_link_to_member(sfOutputEscaper $member = null)
{
  if (function_exists('op_link_to_member'))
  {
    return op_link_to_member($member);
  }

  if ($member && $member->id)
  {
    return link_to($member->name, sprintf('@obj_member_profile?id=%d', $member->id));
  }

  return '';
}

function op_api_message($messageList, $member, $useIsReadFlag = false)
{
  $message = $messageList->getSendMessageData();
  $body = preg_replace(array('/&lt;op:.*?&gt;/', '/&lt;\/op:.*?&gt;/'), '', $message->getDecoratedMessageBody());
  $body = preg_replace('/http.:\/\/maps\.google\.co[[:graph:]]*/', '', $body);
  $body = op_auto_link_text($body);
  $imagePath = null;
  $imageTag = null;
  $image = $message->getMessageFile();

  if (0 < count($image))
  {
    $imageTag = image_tag_sf_image($image[0]->getFile(), array('size' => '76x76'));
    $imagePath = sf_image_path($image[0]->getFile());
  }

  $data = array(
    'id'          => $message->getId(),
    'member'      => op_api_member($member),
    'subject'     => $message->getSubject(),
    'body'        => nl2br($body),
    'summary'     => op_truncate(op_decoration($body, true), 25, '...'),
    'image_path'  => $imagePath,
    'image_tag'   => $imageTag,
    'created_at'  => $message->getCreatedAt(),
    'formatted_date' => get_formatted_date($message->getCreatedAt()),
    'chainUrl'    => app_url_for('pc_frontend', '@messageChain?id='.$member->getId()),
  );

  if ($useIsReadFlag)
  {
    $data['is_read'] = $message->getIsSender() ? (bool) $messageList->getIsRead() : null;
  }

  return $data;
}

function get_formatted_date($date)
{
  return op_format_date($date, 'yyyy/MM/dd (EEE)');
}
