<?php


namespace Mzh\Validate\Annotations;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Validation extends AbstractAnnotation
{
    /**
     * 模块
     * @var string
     */
    public  $mode = '';
    /**
     * 验证器
     * @var string
     */
    public  $validate = '';
    /**
     * 场景
     * @var string
     */
    public  $value = '';
    /**
     * 场景
     * @var string
     */
    public  $scene = '';
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
     * 安全模式严格按照规则字段，如果多字段会抛出异常
     * @var bool
     */
    public $security = false;

    /**
     * 是否批量验证
     * @var bool
     */
    public  $batch = false;
    /**
     * 验证哪个参数
     * @var string
     */
    public  $field = "data";

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('scene', $value);
    }
}
