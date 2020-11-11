<?php


namespace Feed\Controller;

use Feed\Repository\RecordRepository;
use Feed\Service\Badges;
use Feed\Service\Database;

class RecordsController extends LayoutController
{
    public function get()
    {
        $collection = $this->f('collection');

        $this->validateCollection($collection);

        $sender = $this->f('sender');
        $thread = $this->f('thread');
        $recipient = $this->f('recipient');
        $search = $this->f('search');
        $id = $this->f('id');
        $limit = $this->f('limit');
        $order = $this->f('order', 'desc');

        /** @var Database $database */
        $database = $this->s('database');

        $records = $database->getRecords([
            'collection' => $collection,
            'recipient' => $recipient,
            'sender' => $sender,
            'thread' => $thread,
            'search' => $search,
            'id' => $id,
            'limit' => $limit,
            'order' => $order
        ]);

        /** @var RecordRepository $repository */
        $repository = $this->s('repository.record');

        $this->setContent([
            'records' => $repository->formatCollection($records)
        ]);
    }

    public function delete()
    {
        $collection = $this->f('collection');
        $recipient = $this->f('recipient');
        $badge_user = $this->f('badge_user');

        $this->validateCollection($collection);
        $this->validateNotEmpty($recipient, 'recipient');

        /** @var Database $database */
        $database = $this->s('database');

        $deleted = $database->deleteAll($collection, $recipient);

        if ($deleted && $this->hasBadges()){
            if (!$badge_user) {
                $badge_user = $recipient;
            }

            $this->getProxy()->deferCallable(function () use ($collection, $badge_user) {
                /** @var Badges $badges */
                $badges = $this->s('badges');
                $badges->deleteAll($collection, $badge_user);
            });
        }
    }

    public function post()
    {
        $collection = $this->f('collection');
        $thread = $this->f('thread');
        $new_thread = $this->f('new_thread');

        $this->validateCollection($collection);
        $this->validateNotEmpty($thread, 'thread');
        $this->validateNotEmpty($new_thread, 'new_thread');

        /** @var Database $database */
        $database = $this->s('database');

        $this->setStatus($database->update($thread, $new_thread, $collection));
    }
}