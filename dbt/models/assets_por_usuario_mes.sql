SELECT
    u.name as usuario,
    DATE_TRUNC('month', a.created_at) as mes,
    COUNT(a.id) as total_assets
FROM {{ source('dam', 'assets') }} a
LEFT JOIN {{ source('dam', 'users') }} u ON a.user_id = u.id
GROUP BY u.name, DATE_TRUNC('month', a.created_at)
