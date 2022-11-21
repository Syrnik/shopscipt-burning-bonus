<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2022
 * @license Webasyst
 */

declare(strict_types=1);

/**
 *
 */
class shopBurningbonusAffiliateModel extends shopAffiliateTransactionModel
{
    /**
     * @param string $date
     * @param int $limit
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getBurning(string $date, int $limit = 10): array
    {
        $data = $this->queryBurning($date, ['limit' => "$limit"])->fetchAll();

        if (!$data) return [];

        array_walk($data, fn(&$row) => $row = self::typecastBurningRow($row));

        return $data;
    }

    /**
     * @param string $date
     * @param array $params
     * @return waDbResultSelect
     * @throws waDbException
     * @throws waException
     */
    public function queryBurning(string $date, array $params = []): waDbResultSelect
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $date))
            throw new waException('$date должна быть \'гггг-мм-дд\' или \'гггг-мм-дд чч:мм:сс\'');

        if (strlen($date) < 19) $date .= ' 00:00:00';

        $q_params = ['date' => $date];

        $query = "SELECT last_date.contact_id, balance.balance, COALESCE(decrease.decsum,0) as decrision, balance.balance-COALESCE(decrease.decsum,0) AS to_burn " .
            "FROM (" .
            "SELECT contact_id, MAX(create_datetime) as last_date FROM shop_affiliate_transaction WHERE create_datetime < s:date GROUP BY contact_id) AS last_date " .
            "LEFT JOIN shop_affiliate_transaction AS balance ON balance.contact_id = last_date.contact_id AND balance.create_datetime = last_date.last_date " .
            "LEFT JOIN (" .
            "SELECT contact_id, SUM(ABS(amount)) AS decsum FROM shop_affiliate_transaction WHERE create_datetime >= s:date AND amount<0 GROUP BY contact_id ) AS decrease " .
            "ON decrease.contact_id=last_date.contact_id " .
            "WHERE";

        if ($contact_id = $params['contact_id'] ?? null) {
            $query .= ' last_date.contact_id=i:contact_id';
            $q_params['contact_id'] = $contact_id;
        } else $query .= " last_date.contact_id NOT IN (SELECT contact_id FROM shop_affiliate_transaction WHERE balance <= 0 AND create_datetime >= s:date GROUP BY contact_id)";

        if ($order = $params['order'] ?? null) $query .= " ORDER BY $order";

        if ($limit = $params['limit'] ?? null) $query .= " LIMIT $limit";

        return $this->query($query, $q_params);
    }

    /**
     * @param array $row
     * @return array{contact_id:int, balance:float, decrision:float, to_burn:float}
     */
    public function typecastBurningRow(array $row): array
    {
        foreach ($row as $key => $datum) {
            if ($key === 'contact_id') $row[$key] = (int)$datum;
            else $row[$key] = (float)$datum;
        }

        return $row;
    }

    /**
     * @param int $contact_id
     * @param string $date
     * @return array{contact_id:int, balance:float, decrision:float, to_burn:float}
     * @throws waDbException
     * @throws waException
     */
    public function getBurningById(int $contact_id, string $date): array
    {
        $data = $this->queryBurning($date, ['contact_id' => $contact_id])->fetchAll();
        return $data ? self::typecastBurningRow($data[0]) : [];
    }
}
