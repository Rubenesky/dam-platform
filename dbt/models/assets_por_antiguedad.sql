SELECT
    CASE
        WHEN created_at >= NOW() - INTERVAL '30 days' THEN 'Menos de 30 días'
        WHEN created_at >= NOW() - INTERVAL '90 days' THEN '30-90 días'
        ELSE 'Más de 90 días'
    END as antiguedad,
    COUNT(*) as total
FROM {{ source('dam', 'assets') }}
GROUP BY antiguedad
