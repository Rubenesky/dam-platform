SELECT
    COUNT(*) as total_sin_categoria,
    ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM {{ source('dam', 'assets') }}), 2) as porcentaje
FROM {{ source('dam', 'assets') }} a
WHERE NOT EXISTS (
    SELECT 1 FROM {{ source('dam', 'asset_category') }} ac
    WHERE ac.asset_id = a.id
)
