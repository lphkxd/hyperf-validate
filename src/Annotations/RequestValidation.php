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
    public  $mode = '';
    /**
     * 场景
     * @var string
     */
    public  $scene = '';
    /**
     * 场景
     * @var string
     */
    public  $value = '';
    /**
     * 是否过滤多余字段
     * @var bool
     */
    public  $filter = false;
    /**
     * 过滤是否抛出异常
     * @var bool
     */
    public  $throw = false;
    /**
     * 是否批量验证
     * @var bool
     */
    public  $batch = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('scene', $value);
    }
}
