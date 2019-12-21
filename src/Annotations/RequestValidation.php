<?php


namespace Mzh\Validate\Annotations;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class RequestValidation extends AbstractAnnotation
{
    /**
     * 模块
     * @var string
     */
    public string $mode = '';
    /**
     * 场景
     * @var string
     */
    public string $scene = '';
    /**
     * 场景
     * @var string
     */
    public string $value = '';
    /**
     * 是否过滤多余字段
     * @var bool
     */
    public bool $filter = false;
    /**
     * 过滤是否抛出异常
     * @var bool
     */
    public bool $throw = false;
    /**
     * 是否批量验证
     * @var bool
     */
    public bool $batch = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('scene', $value);
    }
}