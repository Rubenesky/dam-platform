SELECT
    DATE_TRUNC('month', created_at) as mes,
    COUNT(*) as total,
    COUNT(CASE WHEN status = 'error' THEN 1 END) as errores,
    ROUND(
        COUNT(CASE WHEN status = 'error' THEN 1 END) * 100.0 / COUNT(*),
        2
    ) as tasa_error_porcentaje
FROM {{ source('dam', 'assets') }}
GROUP BY DATE_TRUNC('month', created_at)
ORDER BY mes
