SELECT
    c.name as categoria,
    COUNT(ac.asset_id) as total_assets
FROM {{ source('dam', 'categories') }} c
LEFT JOIN {{ source('dam', 'asset_category') }} ac ON ac.category_id = c.id
GROUP BY c.name
ORDER BY total_assets DESC
