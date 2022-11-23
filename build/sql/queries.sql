## Объединяющий запрос
# 1. Баланс на указанную дату
# 2. Актуальный баланс
#    Условие После указанной даты не было перехода через 0 (т.е. баланс не обнулялся)
# 3. Сумма списаний после указанной даты
# 4. Разница между было и сумма списаний — количество протухших
EXPLAIN
SELECT
    last_date.contact_id,
    balance.balance,
    actual_balance.balance as actual_balance, COALESCE(decrease.decsum,0) as decrision,
    balance.balance-COALESCE(decrease.decsum,0) AS to_burn
FROM (SELECT contact_id, MAX(create_datetime) as last_date
      FROM shop_affiliate_transaction
      WHERE create_datetime < '2021-11-19 00:00:00'
      GROUP BY contact_id) AS last_date
         LEFT JOIN shop_affiliate_transaction AS balance
                   ON balance.contact_id = last_date.contact_id AND balance.create_datetime = last_date.last_date
         LEFT JOIN (SELECT last_transaction.contact_id, actual_balance.balance
                    FROM (SELECT contact_id, MAX(create_datetime) AS last_transaction_date
                          FROM shop_affiliate_transaction
                          GROUP BY contact_id) AS last_transaction
                        LEFT JOIN shop_affiliate_transaction AS actual_balance
                            ON actual_balance.contact_id=last_transaction.contact_id
                                   AND actual_balance.create_datetime=last_transaction.last_transaction_date ) AS actual_balance
            ON last_date.contact_id=actual_balance.contact_id
         LEFT JOIN (SELECT contact_id, SUM(ABS(amount)) AS decsum
                    FROM shop_affiliate_transaction
                    WHERE create_datetime >= '2021-11-19 00:00:00' AND amount<0
                    GROUP BY contact_id
) AS decrease
                   ON decrease.contact_id=last_date.contact_id
WHERE
    actual_balance.balance > 0
    AND balance.balance-COALESCE(decrease.decsum,0) > 0
    AND last_date.contact_id NOT IN (SELECT contact_id
                                   FROM shop_affiliate_transaction
                                   WHERE balance <= 0
                                     AND create_datetime >= '2021-11-19 00:00:00'
                                   GROUP BY contact_id);
EXPLAIN
SELECT last_transaction.contact_id, last_transaction.last_transaction_date, actual_balance.balance FROM
(SELECT contact_id, MAX(create_datetime) AS last_transaction_date
FROM shop_affiliate_transaction
GROUP BY contact_id) AS last_transaction
LEFT JOIN shop_affiliate_transaction AS actual_balance
    ON actual_balance.contact_id=last_transaction.contact_id
           AND actual_balance.create_datetime=last_transaction.last_transaction_date
WHERE balance > 0
;

SELECT
    last_date.contact_id,
    balance.balance, COALESCE(decrease.decsum,0) as decrision,
    balance.balance-COALESCE(decrease.decsum,0) AS to_burn
FROM (SELECT contact_id, MAX(create_datetime) as last_date
      FROM shop_affiliate_transaction
      WHERE create_datetime < '2021-11-19 00:00:00'
      GROUP BY contact_id) AS last_date
         LEFT JOIN shop_affiliate_transaction AS balance
                   ON balance.contact_id = last_date.contact_id AND balance.create_datetime = last_date.last_date
         LEFT JOIN (SELECT contact_id, SUM(ABS(amount)) AS decsum
                    FROM shop_affiliate_transaction
                    WHERE create_datetime >= '2021-11-19 00:00:00' AND amount<0
                    GROUP BY contact_id
) AS decrease
                   ON decrease.contact_id=last_date.contact_id
WHERE last_date.contact_id =3
ORDER BY contact_id;



## Баланс на указанную дату
EXPLAIN SELECT balance.balance, last_date.contact_id, last_date.last_date
        FROM
            (
                SELECT contact_id, MAX(create_datetime) as last_date
                FROM shop_affiliate_transaction
                         FORCE KEY FOR GROUP BY (contact_id)
                WHERE create_datetime < '2022-10-02 00:00:00'
                GROUP BY contact_id
            ) AS last_date
                LEFT JOIN shop_affiliate_transaction AS balance
                FORCE KEY FOR JOIN (contact_id)
                          ON balance.contact_id=last_date.contact_id AND balance.create_datetime=last_date.last_date
WHERE balance.balance > 0;

SELECT contact_id, MAX(create_datetime) as last_date
FROM shop_affiliate_transaction
WHERE create_datetime < '2021-10-19 00:00:00'
GROUP BY contact_id;


/*
 Все последние начисления
 */
EXPLAIN SELECT transaction.id, transaction.contact_id, transaction.balance, last_transaction_data.last_date
        FROM shop_affiliate_transaction AS transaction,
             (    SELECT id, contact_id, MAX(create_datetime) as last_date from shop_affiliate_transaction GROUP BY contact_id
             ) AS last_transaction_data
        WHERE transaction.contact_id=last_transaction_data.contact_id AND transaction.create_datetime=last_transaction_data.last_date;

## Сумма списаний начиная с конкретной даты
SELECT contact_id, SUM(ABS(amount)) AS decsum
FROM shop_affiliate_transaction
WHERE create_datetime >= '2021-09-01 00:00:00' AND amount<0
GROUP BY contact_id
ORDER BY contact_id;

## Все, у кого был отрицательный или нулевой баланс после указанной даты
SELECT contact_id
FROM shop_affiliate_transaction
WHERE balance <=0 AND create_datetime >= '2021-09-01 00:00:00'
GROUP BY contact_id;

## Баланс на указанную дату


/*
 Выбрали всех контактов, у которых есть начисления до пороговой даты и нет обнуления баланса за отчётный период
 То есть это те, у кого потенциально должны сгореть бонусы
 */
EXPLAIN SELECT * from shop_affiliate_transaction
        WHERE create_datetime <= '2021-09-01 00:00:00'
          AND contact_id NOT IN
              (SELECT contact_id
               FROM shop_affiliate_transaction
               WHERE balance <=0 AND create_datetime >= '2021-09-01 00:00:00'
               GROUP BY contact_id)
        GROUP BY contact_id;
