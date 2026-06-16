SELECT
    status,
    COUNT(*) as total,
    ROUND(COUNT(*) * 100.0 / SUM(COUNT(*)) OVER (), 2) as porcentaje
FROM {{ source('dam', 'assets') }}
GROUP BY status
