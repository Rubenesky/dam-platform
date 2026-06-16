SELECT
    TO_CHAR(created_at, 'Day') as dia_semana,
    EXTRACT(DOW FROM created_at) as numero_dia,
    COUNT(*) as total_acciones
FROM {{ source('dam', 'activity_log') }}
GROUP BY TO_CHAR(created_at, 'Day'), EXTRACT(DOW FROM created_at)
ORDER BY numero_dia
