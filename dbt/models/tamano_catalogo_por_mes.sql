SELECT
    DATE_TRUNC('month', created_at) as mes,
    COUNT(*) as assets_nuevos,
    SUM(COUNT(*)) OVER (ORDER BY DATE_TRUNC('month', created_at)) as total_acumulado,
    ROUND(SUM(size) / 1024.0 / 1024.0, 2) as tamano_mb
FROM {{ source('dam', 'assets') }}
GROUP BY DATE_TRUNC('month', created_at)
ORDER BY mes
