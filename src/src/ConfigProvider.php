<?php
namespace Mzh\Validate;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ]
                ]
            ]
        ];
    }
}
