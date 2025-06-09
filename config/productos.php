<?php

return [
    'stock_minimo' => 5,
    'items_por_pagina' => 20,
    
    'estados_permitidos' => [
        'disponible',
        'en_uso', 
        'reservado'
    ],
    
    'formato_codigo' => 'INV-{año}-{0000}'
];