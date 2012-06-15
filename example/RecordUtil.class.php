<?php

class RecordUtil extends DoctrineRecordUtil
{
    /**
     * Create an Account record
     *
     * @return Account
     */
    public static function createAccount($data = array(), $save = true)
    {
        $default = array(
            'status' => Account::$STATUS_ACTIVATED
        );
        $data = array_merge($default, $data);

        return parent::create(new Account(), self::replace($data, uniqid('account-', true)), $save);
    }

    /**
     * Create an Event record
     *
     * @return Event
     */
    public static function createEvent($data = array(), $save = true)
    {
        $default = array(
            'title'         => 'TITLE-%uniqid%',
            'sub_title'     => 'subtitle-%uniqid%',
            'venue'         => 'VenueVenueVenue',
            'category_id'   => 1,
            'is_lock'       => 0,
            'is_published'  => 1,
            'is_deleted'    => 0,
            'start_at'      => date('Y-m-d H:i:s', time() + 86400 * 30),
            'end_at'        => date('Y-m-d H:i:s', time() + (86400 * 30) + 21600),
            'sale_start_at' => date('Y-m-d H:i:s', time() - 3600),
            'sale_end_at'   => date('Y-m-d H:i:s', time() + 86400 * 20),
            'country'       => 'JP',
            'currency'      => 'JPY',
        );
        $data = array_merge($default, $data);

        return parent::create(new Event(), self::replace($data, uniqid('event-', true)), $save);
    }

    /**
     * Create an EventOwner record
     *
     * @return EventOwner
     */
    public static function createEventOwner($data = array(), $save = true)
    {
        $default = array(
            'is_approved' => 1,
            'is_display'  => 1,
        );
        $data = array_merge($default, $data);

        return parent::create(new EventOWner(), self::replace($data, uniqid('event_owner-', true)), $save);
    }

    /**
     * Create an EventParticipant record
     *
     * @return EventParticipant
     */
    public static function createEventParticipant($data = array(), $save = true)
    {
        $default = array(
            'status' => EventParticipant::$STATUS_ACCEPTED,
        );
        $data = array_merge($default, $data);

        return parent::create(new EventParticipant(), self::replace($data, uniqid('event_participant-', true)), $save);
    }

    /**
     * 配列のuniqidを再帰的に置換する
     */
    protected static function replace($data, $uniqid)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::replace($value, $uniqid);
            }
        }
        elseif (is_string($data)) {
            $data = str_replace('%uniqid%', $uniqid, $data);
        }

        return $data;
    }
}