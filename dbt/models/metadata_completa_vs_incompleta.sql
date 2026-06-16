SELECT
    CASE
        WHEN am.title IS NOT NULL AND am.description IS NOT NULL THEN 'Completa'
        WHEN am.title IS NOT NULL OR am.description IS NOT NULL THEN 'Parcial'
        ELSE 'Sin metadata'
    END as estado_metadata,
    COUNT(*) as total
FROM {{ source('dam', 'assets') }} a
LEFT JOIN {{ source('dam', 'asset_metadata') }} am ON am.asset_id = a.id
GROUP BY estado_metadata
