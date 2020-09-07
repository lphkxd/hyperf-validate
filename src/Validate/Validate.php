<?php
declare (strict_types=1);

namespace Mzh\Validate\Validate;

use Hyperf\DbConnection\Db;
use Hyperf\Utils\Str;
use Mzh\Validate\Exception\ValidateException;

class Validate
{
    /**
     * 自定义验证类型
     * @var array
     */
    protected $type = [];

    /**
     * 验证类型别名
     * @var array
     */
    protected $alias = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', 'same' => 'eq',
    ];

    /**
     * 当前验证规则
     * @var array
     */
    protected $rule = [];

    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];

    /**
     * 验证字段描述
     * @var array
     */
    protected $field = [];

    /**
     * 默认规则提示
     * @var array
     */
    protected $typeMsg = [

        'must' => ':attribute 必须',
        'mobile' => ':attribute not a valid mobile',
        'token' => 'invalid token',
        'require' => ':attribute不能为空',
        'number' => ':attribute必须是数字',
        'integer' => ':attribute必须是整数',
        'float' => ':attribute必须是浮点数',
        'boolean' => ':attribute必须是布尔值',
        'email' => ':attribute格式不符',
        'array' => ':attribute必须是数组',
        'accepted' => ':attribute必须是yes、on或者1',
        'date' => ':attribute格式不符合',
        'file' => ':attribute不是有效的上传文件',
        'image' => ':attribute不是有效的图像文件',
        'alpha' => ':attribute只能是字母',
        'alphaNum' => ':attribute只能是字母和数字',
        'alphaDash' => ':attribute只能是字母、数字和下划线_及破折号-',
        'activeUrl' => ':attribute不是有效的域名或者IP',
        'chs' => ':attribute只能是汉字',
        'chsAlpha' => ':attribute只能是汉字、字母',
        'chsAlphaNum' => ':attribute只能是汉字、字母和数字',
        'chsDash' => ':attribute只能是汉字、字母、数字和下划线_及破折号-',
        'url' => ':attribute不是有效的URL地址',
        'ip' => ':attribute不是有效的IP地址',
        'dateFormat' => ':attribute必须使用日期格式 :rule',
        'in' => ':attribute必须在 :rule 范围内',
        'notIn' => ':attribute不能在 :rule 范围内',
        'between' => ':attribute只能在 :1 - :2 之间',
        'notBetween' => ':attribute不能在 :1 - :2 之间',
        'length' => ':attribute长度不符合要求 :rule',
        'max' => ':attribute长度不能超过 :rule',
        'min' => ':attribute长度不能小于 :rule',
        'after' => ':attribute日期不能小于 :rule',
        'before' => ':attribute日期不能超过 :rule',
        'expire' => '不在有效期内 :rule',
        'allowIp' => '不允许的IP访问',
        'denyIp' => '禁止的IP访问',
        'confirm' => ':attribute和确认字段:2不一致',
        'confirmed' => ':attribute和确认字段:2不一致',
        'different' => ':attribute和比较字段:2不能相同',
        'egt' => ':attribute必须大于等于 :rule',
        'gt' => ':attribute必须大于 :rule',
        'elt' => ':attribute必须小于等于 :rule',
        'lt' => ':attribute必须小于 :rule',
        'eq' => ':attribute必须等于 :rule',
        'regex' => ':attribute不符合指定规则',
        'method' => '无效的请求类型',
        'fileSize' => '上传文件大小不符',
        'fileExt' => '上传文件后缀不符',
        'fileMime' => '上传文件类型不符',
        'unique' => ':attribute 已存在',
        'arrayHasOnlyInts' => ':attribute 只允许为array(int,int)',
        'intOrArrayInt' => ':attribute 只允许为int类型或array(int,int)',
        'afterWith' => ':attribute cannot be less than :rule',
        'beforeWith' => ':attribute cannot exceed :rule',

    ];

    /**
     * 当前验证场景
     * @var string
     */
    protected $currentScene;

    /**
     * 内置正则验证规则
     * @var array
     */
    protected $defaultRegex = [
        'alpha' => '/^[A-Za-z]+$/',
        'alphaNum' => '/^[A-Za-z0-9]+$/',
        'alphaDash' => '/^[A-Za-z0-9\-\_]+$/',
        'chs' => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'chsAlpha' => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',
        'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u',
        'chsDash' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u',
        'mobile' => '/^1[3-9]\d{9}$/',
        'idCard' => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        'zip' => '/\d{6}/',
    ];

    /**
     * Filter_var 规则
     * @var array
     */
    protected $filter = [
        'email' => FILTER_VALIDATE_EMAIL,
        'ip' => [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
        'integer' => FILTER_VALIDATE_INT,
        'url' => FILTER_VALIDATE_URL,
        'macAddr' => FILTER_VALIDATE_MAC,
        'float' => FILTER_VALIDATE_FLOAT,
    ];

    /**
     * 验证场景定义
     * @var array
     */
    protected $scene = [];

    /**
     * 验证失败错误信息
     * @var string|array
     */
    protected $error = [];

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batch = false;

    /**
     * 验证失败是否抛出异常
     * @var bool
     */
    protected $failException = false;

    /**
     * 场景需要验证的规则
     * @var array
     */
    protected $only = [];

    /**
     * 场景需要移除的验证规则
     * @var array
     */
    protected $remove = [];

    /**
     * 场景需要追加的验证规则
     * @var array
     */
    protected $append = [];

    /**
     * 验证正则定义
     * @var array
     */
    protected $regex = [];

    /**
     * Db对象
     * @var Db
     */
    protected $db;

    /**
     * 语言对象
     */
    protected $lang;

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * @var Closure[]
     */
    protected static $maker = [];

    /**
     * 构造方法
     * @access public
     */
    public function __construct()
    {
        if (!empty(static::$maker)) {
            foreach (static::$maker as $maker) {
                call_user_func($maker, $this);
            }
        }
    }

    /**
     * 设置服务注入
     * @access public
     * @param Closure $maker
     * @return void
     */
    public static function maker(Closure $maker)
    {
        static::$maker[] = $maker;
    }

    /**
     * 设置Lang对象
     * @access public
     * @param Lang $lang Lang对象
     * @return void
     */
    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
    }

    /**
     * 设置Db对象
     * @access public
     * @param Db $db Db对象
     * @return void
     */
    public function setDb(Db $db)
    {
        $this->db = $db;
    }

    /**
     * 设置Request对象
     * @access public
     * @param Request $request Request对象
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * 添加字段验证规则
     * @access protected
     * @param string|array $name 字段名称或者规则数组
     * @param mixed $rule 验证规则或者字段描述信息
     * @return $this
     */
    public function rule($name, $rule = '')
    {
        if (is_array($name)) {
            $this->rule = $name + $this->rule;
            if (is_array($rule)) {
                $this->field = array_merge($this->field, $rule);
            }
        } else {
            $this->rule[$name] = $rule;
        }

        return $this;
    }

    /**
     * 注册验证（类型）规则
     * @access public
     * @param string $type 验证规则类型
     * @param callable $callback callback方法(或闭包)
     * @param string $message 验证失败提示信息
     * @return $this
     */
    public function extend(string $type, callable $callback = null, string $message = null)
    {
        $this->type[$type] = $callback;

        if ($message) {
            $this->typeMsg[$type] = $message;
        }

        return $this;
    }

    /**
     * 设置验证规则的默认提示信息
     * @access public
     * @param string|array $type 验证规则类型名称或者数组
     * @param string $msg 验证提示信息
     * @return void
     */
    public function setTypeMsg($type, string $msg = null): void
    {
        if (is_array($type)) {
            $this->typeMsg = array_merge($this->typeMsg, $type);
        } else {
            $this->typeMsg[$type] = $msg;
        }
    }

    /**
     * 设置提示信息
     * @access public
     * @param array $message 错误信息
     * @return Validate
     */
    public function message(array $message)
    {
        $this->message = array_merge($this->message, $message);

        return $this;
    }

    /**
     * 设置验证场景
     * @access public
     * @param string $name 场景名
     * @return $this
     */
    public function scene(string $name)
    {
        // 设置当前场景
        $this->currentScene = $name;

        return $this;
    }


    /**
     * 判断是否存在某个验证场景
     * @access public
     * @param string $name 场景名
     * @return bool
     */
    public function hasScene(string $name): bool
    {
        return isset($this->scene[$name]) || method_exists($this, 'scene' . $name);
    }

    /**
     * 设置批量验证
     * @access public
     * @param bool $batch 是否批量验证
     * @return $this
     */
    public function batch(bool $batch = true)
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    public function failException(bool $fail = true)
    {
        $this->failException = $fail;

        return $this;
    }

    /**
     * 指定需要验证的字段列表
     * @access public
     * @param array $fields 字段名
     * @return $this
     */
    public function only(array $fields)
    {
        $this->only = $fields;

        return $this;
    }

    /**
     * 移除某个字段的验证规则
     * @access public
     * @param string|array $field 字段名
     * @param mixed $rule 验证规则 true 移除所有规则
     * @return $this
     */
    public function remove($field, $rule = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $rule) {
                if (is_int($key)) {
                    $this->remove($rule);
                } else {
                    $this->remove($key, $rule);
                }
            }
        } else {
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }

            $this->remove[$field] = $rule;
        }

        return $this;
    }

    /**
     * 追加某个字段的验证规则
     * @access public
     * @param string|array $field 字段名
     * @param mixed $rule 验证规则
     * @return $this
     */
    public function append($field, $rule = null)
    {
        if (is_array($field)) {
            foreach ($field as $key => $rule) {
                $this->append($key, $rule);
            }
        } else {
            if (is_string($rule)) {
                $rule = explode('|', $rule);
            }

            $this->append[$field] = $rule;
        }

        return $this;
    }

    /**
     * 数据自动验证
     * @access public
     * @param array $data 数据
     * @param array $rules 验证规则
     * @return bool
     */
    public function check(array $data, array $rules = [], $prefix = ''): bool
    {

        $this->error = [];

        if ($this->currentScene) {
            $rules = $this->getScene($this->currentScene);
        }

        if (empty($rules)) {
            // 读取验证规则
            $rules = $this->rule;
        }

        foreach ($this->append as $key => $rule) {
            if (!isset($rules[$key])) {
                $rules[$key] = $rule;
            }
        }
        foreach ($rules as $key => $rule) {
            // field => 'rule1|rule2...' field => ['rule1','rule2',...]
            if (is_numeric($key)) {
                $key = $rule;
                $rule = $this->rule[$key] ?? '';
            }
            if (is_array($rule)) {
                $rule = array_filter($rule);
            }

            if (empty($rule)) {
                continue;
            }
            if (strpos($key, '|')) {
                // 字段|描述 用于指定属性名称
                [$key, $title] = explode('|', $key);
            } else {
                $title = ($this->field[$prefix][$key] ?? $this->field[$key] ?? trim($prefix . '.' . $key, '.'));
            }
            // 场景检测
            if (!empty($this->only) && !in_array($key, $this->only)) {
                continue;
            }
            // 获取数据 支持二维数组
            $value = $this->getDataValue($data, $key);
            switch (true) {
                case $rule instanceof Closure:
                    $result = call_user_func_array($rule, [$value, $data]);
                    break;
                case $rule instanceof ValidateRule:
                    $result = $this->checkItem($key, $value, $rule->getRule(), $data, $rule->getTitle() ?: $title, $rule->getMsg());
                    break;
                case is_array($rule) && is_integer(key($rule)):      #判断是否是二维数组验证
                    $result = $this->checkItem($key, $value, $rule, $data, $title);
                    break;
                case is_array($rule):
                      if (!isset($data[$key]) || !is_array($data[$key])) {
                        $result = '参数' . $key . "必须为二维数组";
                        break;
                    }
                    $ruleStr = [];
                    foreach ($rule as $ruleKey => $itemRule) {
                        $field = str_replace('*.', '', $ruleKey);
                        $ruleStr[$field] = $itemRule;
                    }
                    foreach ($data[$key] as $item) {
                        if (!is_array($item)) {
                            $result = $key . "必须为二维数组";
                            break;
                        }
                        $result = $this->check($item, $ruleStr, $key);
                        if ($result !== true) return false;
                    }
                    break;
                default:
                    $result = $this->checkItem($key, $value, $rule, $data, $title);
                    break;
            }
            if (true !== $result) {
                // 没有返回true 则表示验证失败
                if (!empty($this->batch)) {
                    // 批量验证
                    $this->error[$key] = $result;
                } elseif ($this->failException) {
                    throw new ValidateException($result);
                } else {
                    $this->error = $result;
                    return false;
                }
            }
        }

        if (!empty($this->error)) {
            if ($this->failException) {
                throw new ValidateException($this->error);
            }
            return false;
        }

        return true;
    }


    /**
     * 根据验证规则验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rules 验证规则
     * @return bool
     */
    public function checkRule($value, $rules): bool
    {
        if ($rules instanceof Closure) {
            return call_user_func_array($rules, [$value]);
        } elseif ($rules instanceof ValidateRule) {
            $rules = $rules->getRule();
        } elseif (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        foreach ($rules as $key => $rule) {
            if ($rule instanceof Closure) {
                $result = call_user_func_array($rule, [$value]);
            } else {
                // 判断验证类型
                [$type, $rule] = $this->getValidateType($key, $rule);

                $callback = $this->type[$type] ?? [$this, $type];

                $result = call_user_func_array($callback, [$value, $rule]);
            }

            if (true !== $result) {
                if ($this->failException) {
                    throw new ValidateException($result);
                }

                return $result;
            }
        }

        return true;
    }

    /**
     * 验证单个字段规则
     * @access protected
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @param mixed $rules 验证规则
     * @param array $data 数据
     * @param string $title 字段描述
     * @param array $msg 提示信息
     * @return mixed
     */
    protected function checkItem(string $field, $value, $rules, $data, string $title = '', array $msg = [])
    {
        if (isset($this->remove[$field]) && true === $this->remove[$field] && empty($this->append[$field])) {
            // 字段已经移除 无需验证
            return true;
        }
        // 支持多规则验证 require|in:a,b,c|... 或者 ['require','in'=>'a,b,c',...]
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        if (isset($this->append[$field])) {
            // 追加额外的验证规则
            $rules = array_unique(array_merge($rules, $this->append[$field]), SORT_REGULAR);
        }

        if (empty($rules)) {
            return true;
        }

        $i = 0;
        foreach ($rules as $key => $rule) {
            if ($rule instanceof Closure) {
                $result = call_user_func_array($rule, [$value, $data]);
                $info = is_numeric($key) ? '' : $key;
            } else {
                // 判断验证类型

                [$type, $rule, $info] = $this->getValidateType($key, $rule);
                if (isset($this->append[$field]) && in_array($info, $this->append[$field])) {
                } elseif (isset($this->remove[$field]) && in_array($info, $this->remove[$field])) {
                    // 规则已经移除
                    $i++;
                    continue;
                }
                if (isset($this->type[$type])) {
                    $result = call_user_func_array($this->type[$type], [$value, $rule, $data, $field, $title]);
                } elseif ('must' == $info || 0 === strpos($info, 'require') || (!is_null($value) && '' !== $value)) {
                    $result = call_user_func_array([$this, $type], [$value, $rule, $data, $field, $title]);
                } else {
                    $result = true;
                }
            }

            if (false === $result) {
                // 验证失败 返回错误信息
                if (!empty($msg[$i])) {
                    $message = $msg[$i];
                    if (is_string($message) && strpos($message, '{%') === 0) {
                        $message = $this->lang->get(substr($message, 2, -1));
                    }
                } else {
                    $message = $this->getRuleMsg($field, $title, $info, $rule);
                }


                return $message;
            } elseif (true !== $result) {
                // 返回自定义错误信息
                if (is_string($result) && false !== strpos($result, ':')) {
                    $result = str_replace(':attribute', $title, $result);

                    if (strpos($result, ':rule') && is_scalar($rule)) {
                        $result = str_replace(':rule', (string)$rule, $result);
                    }
                }

                return $result;
            }
            $i++;
        }

        return $result;
    }

    /**
     * 获取当前验证类型及规则
     * @access public
     * @param mixed $key
     * @param mixed $rule
     * @return array
     */
    protected function getValidateType($key, $rule): array
    {
        // 判断验证类型
        if (!is_numeric($key)) {
            if (isset($this->alias[$key])) {
                // 判断别名
                $key = $this->alias[$key];
            }
            return [$key, $rule, $key];
        }

        if (strpos($rule, ':')) {
            [$type, $rule] = explode(':', $rule, 2);
            if (isset($this->alias[$type])) {
                // 判断别名
                $type = $this->alias[$type];
            }
            $info = $type;
        } elseif (method_exists($this, $rule)) {
            $type = $rule;
            $info = $rule;
            $rule = '';
        } else {
            $type = 'is';
            $info = $rule;
        }
        return [$type, $rule, $info];
    }

    /**
     * 验证是否和某个字段的值一致
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @param string $field 字段名
     * @return bool
     */
    public function confirm($value, $rule, array $data = [], string $field = ''): bool
    {
        if ('' == $rule) {
            if (strpos($field, '_confirm')) {
                $rule = strstr($field, '_confirm', true);
            } else {
                $rule = $field . '_confirm';
            }
        }

        return $this->getDataValue($data, $rule) === $value;
    }

    /**
     * 验证是否和某个字段的值一致
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @param string $field 字段名
     * @return bool
     */
    public function confirmed($value, $rule, array $data = [], string $field = ''): bool
    {
        if ('' == $rule) {
            if (strpos($field, '_confirm')) {
                $rule = strstr($field, '_confirm', true);
            } else {
                $rule = $field . '_confirm';
            }
        }
        return $this->getDataValue($data, $rule) === $value;
    }

    /**
     * 验证是否和某个字段的值是否不同
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function different($value, $rule, array $data = []): bool
    {
        return $this->getDataValue($data, $rule) != $value;
    }

    /**
     * 验证是否大于等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function egt($value, $rule, array $data = []): bool
    {
        return $value >= $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否大于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function gt($value, $rule, array $data = []): bool
    {
        return $value > $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否小于等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function elt($value, $rule, array $data = []): bool
    {
        return $value <= $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否小于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function lt($value, $rule, array $data = []): bool
    {
        return $value < $this->getDataValue($data, $rule);
    }

    /**
     * 验证是否等于某个值
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function eq($value, $rule): bool
    {
        return $value == $rule;
    }

    /**
     * 必须验证
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function must($value, $rule = null): bool
    {
        return !empty($value) || '0' == $value;
    }

    /**
     * 验证字段值是否为有效格式
     * @access public
     * @param mixed $value 字段值
     * @param string $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function is($value, string $rule, array $data = []): bool
    {
        switch (Str::camel($rule)) {
            case 'require':
            case 'required':
                // 必须
                $result = !empty($value) || '0' == $value;
                break;
            case 'accepted':
                // 接受
                $result = in_array($value, ['1', 'on', 'yes']);
                break;
            case 'date':
                // 是否是一个有效日期
                $result = false !== strtotime($value);
                break;
            case 'activeUrl':
                // 是否为有效的网址
                $result = checkdnsrr($value);
                break;
            case 'boolean':
            case 'bool':
                // 是否为布尔值
                $result = in_array($value, [true, false, 0, 1, '0', '1'], true);
                break;
            case 'number':
                $result = ctype_digit((string)$value);
                break;
            case 'alphaNum':
                $result = ctype_alnum($value);
                break;
            case 'array':
                // 是否为数组
                $result = is_array($value);
                break;
            case 'file':
                $result = $value instanceof File;
                break;
            case 'image':
                $result = $value instanceof File && in_array($this->getImageType($value->getRealPath()), [1, 2, 3, 6]);
                break;
            case 'token':
                $result = $this->token($value, '__token__', $data);
                break;
            default:
                if (isset($this->type[$rule])) {
                    // 注册的验证规则
                    $result = call_user_func_array($this->type[$rule], [$value]);
                } elseif (function_exists('ctype_' . $rule)) {
                    // ctype验证规则
                    $ctypeFun = 'ctype_' . $rule;
                    $result = $ctypeFun($value);
                } elseif (isset($this->filter[$rule])) {
                    // Filter_var验证规则
                    $result = $this->filter($value, $this->filter[$rule]);
                } else {
                    // 正则验证
                    $result = $this->regex($value, $rule);
                }
        }

        return $result;
    }

    // 判断图像类型
    protected function getImageType($image)
    {
        if (function_exists('exif_imagetype')) {
            return exif_imagetype($image);
        }

        try {
            $info = getimagesize($image);
            return $info ? $info[2] : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 验证表单令牌
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function token($value, string $rule, array $data): bool
    {
        $rule = !empty($rule) ? $rule : '__token__';
        return $this->request->checkToken($rule, $data);
    }

    /**
     * 验证是否为合格的域名或者IP 支持A，MX，NS，SOA，PTR，CNAME，AAAA，A6， SRV，NAPTR，TXT 或者 ANY类型
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function activeUrl(string $value, string $rule = 'MX'): bool
    {
        if (!in_array($rule, ['A', 'MX', 'NS', 'SOA', 'PTR', 'CNAME', 'AAAA', 'A6', 'SRV', 'NAPTR', 'TXT', 'ANY'])) {
            $rule = 'MX';
        }

        return checkdnsrr($value, $rule);
    }

    /**
     * 验证是否有效IP
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则 ipv4 ipv6
     * @return bool
     */
    public function ip($value, string $rule = 'ipv4'): bool
    {
        if (!in_array($rule, ['ipv4', 'ipv6'])) {
            $rule = 'ipv4';
        }

        return $this->filter($value, [FILTER_VALIDATE_IP, 'ipv6' == $rule ? FILTER_FLAG_IPV6 : FILTER_FLAG_IPV4]);
    }

    /**
     * 检测上传文件后缀
     * @access public
     * @param File $file
     * @param array|string $ext 允许后缀
     * @return bool
     */
    protected function checkExt(File $file, $ext): bool
    {
        if (is_string($ext)) {
            $ext = explode(',', $ext);
        }

        return in_array(strtolower($file->extension()), $ext);
    }

    /**
     * 检测上传文件大小
     * @access public
     * @param File $file
     * @param integer $size 最大大小
     * @return bool
     */
    protected function checkSize(File $file, $size): bool
    {
        return $file->getSize() <= (int)$size;
    }

    /**
     * 检测上传文件类型
     * @access public
     * @param File $file
     * @param array|string $mime 允许类型
     * @return bool
     */
    protected function checkMime(File $file, $mime): bool
    {
        if (is_string($mime)) {
            $mime = explode(',', $mime);
        }

        return in_array(strtolower($file->getMime()), $mime);
    }

    /**
     * 验证上传文件后缀
     * @access public
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileExt($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$this->checkExt($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $this->checkExt($file, $rule);
        }

        return false;
    }

    /**
     * 验证上传文件类型
     * @access public
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileMime($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$this->checkMime($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $this->checkMime($file, $rule);
        }

        return false;
    }

    /**
     * 验证上传文件大小
     * @access public
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function fileSize($file, $rule): bool
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                if (!($item instanceof File) || !$this->checkSize($item, $rule)) {
                    return false;
                }
            }
            return true;
        } elseif ($file instanceof File) {
            return $this->checkSize($file, $rule);
        }

        return false;
    }

    /**
     * 验证图片的宽高及类型
     * @access public
     * @param mixed $file 上传文件
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function image($file, $rule): bool
    {
        if (!($file instanceof File)) {
            return false;
        }

        if ($rule) {
            $rule = explode(',', $rule);

            [$width, $height, $type] = getimagesize($file->getRealPath());

            if (isset($rule[2])) {
                $imageType = strtolower($rule[2]);

                if ('jpg' == $imageType) {
                    $imageType = 'jpeg';
                }

                if (image_type_to_extension($type, false) != $imageType) {
                    return false;
                }
            }

            [$w, $h] = $rule;

            return $w == $width && $h == $height;
        }

        return in_array($this->getImageType($file->getRealPath()), [1, 2, 3, 6]);
    }

    /**
     * 验证时间和日期是否符合指定格式
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function dateFormat($value, $rule): bool
    {
        $info = date_parse_from_format($rule, $value);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }

    /**
     * 验证是否唯一
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则 格式：数据表,字段名,排除ID,主键名
     * @param array $data 数据
     * @param string $field 验证字段名
     * @return bool
     */
    public function unique($value, $rule, array $data = [], string $field = ''): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        if (false !== strpos($rule[0], '\\')) {
            // 指定模型类
            $db = new $rule[0];
        } else {
            $db = Db::table($rule[0]);
        }
        $key = $rule[1] ?? $field;
        $map = [];

        if (strpos($key, '^')) {
            // 支持多个字段验证
            $fields = explode('^', $key);
            foreach ($fields as $key) {
                if (isset($data[$key])) {
                    $map[] = [$key, '=', $data[$key]];
                }
            }
        } elseif (isset($data[$field])) {
            $map[] = [$key, '=', $data[$field]];
        } else {
            $map = [];
        }

        $pk = !empty($rule[3]) ? $rule[3] : 'id';

        if (isset($data[$pk])) {
            $map[] = [$pk, '<>', $data[$pk]];
        } elseif (isset($rule[2]) && $rule[2] != '') {
            $map[] = [$pk, '<>', $rule[2]];
        }
        if ($db->where($map)->count()) {
            return false;
        }
        return true;
    }

    /**
     * 使用filter_var方式验证
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function filter($value, $rule): bool
    {
        if (is_string($rule) && strpos($rule, ',')) {
            [$rule, $param] = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = $rule[1] ?? null;
            $rule = $rule[0];
        } else {
            $param = null;
        }

        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

    /**
     * 验证某个字段等于某个值的时候必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireIf($value, $rule, array $data = []): bool
    {
        [$field, $val] = explode(',', $rule);

        if ($this->getDataValue($data, $field) == $val) {
            return !empty($value) || '0' == $value;
        }

        return true;
    }

    /**
     * 通过回调方法验证某个字段是否必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireCallback($value, $rule, array $data = []): bool
    {
        $result = call_user_func_array([$this, $rule], [$value, $data]);

        if ($result) {
            return !empty($value) || '0' == $value;
        }

        return true;
    }

    /**
     * 验证某个字段有值的情况下必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireWith($value, $rule, array $data = []): bool
    {
        $val = $this->getDataValue($data, $rule);

        if (!empty($val)) {
            return !empty($value) || '0' == $value;
        }

        return true;
    }

    /**
     * 验证某个字段没有值的情况下必须
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function requireWithout($value, $rule, array $data = []): bool
    {
        $val = $this->getDataValue($data, $rule);

        if (empty($val)) {
            return !empty($value) || '0' == $value;
        }

        return true;
    }

    /**
     * 验证是否在范围内
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function in($value, $rule): bool
    {
        return in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证是否不在某个范围
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function notIn($value, $rule): bool
    {
        return !in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * between验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function between($value, $rule): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        [$min, $max] = $rule;

        return $value >= $min && $value <= $max;
    }

    /**
     * 使用notbetween验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function notBetween($value, $rule): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }
        [$min, $max] = $rule;

        return $value < $min || $value > $max;
    }

    /**
     * 验证数据长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function length($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        if (is_string($rule) && strpos($rule, ',')) {
            // 长度区间
            [$min, $max] = explode(',', $rule);
            return $length >= $min && $length <= $max;
        }

        // 指定长度
        return $length == $rule;
    }

    /**
     * 验证数据最大长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function max($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length <= $rule;
    }

    /**
     * 验证数据最小长度
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function min($value, $rule): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } elseif ($value instanceof File) {
            $length = $value->getSize();
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length >= $rule;
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function after($value, $rule, array $data = []): bool
    {
        return strtotime($value) >= strtotime($rule);
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function before($value, $rule, array $data = []): bool
    {
        return strtotime($value) <= strtotime($rule);
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function afterWith($value, $rule, array $data = []): bool
    {
        $rule = $this->getDataValue($data, $rule);
        return !is_null($rule) && strtotime($value) >= strtotime($rule);
    }

    /**
     * 验证日期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @param array $data 数据
     * @return bool
     */
    public function beforeWith($value, $rule, array $data = []): bool
    {
        $rule = $this->getDataValue($data, $rule);
        return !is_null($rule) && strtotime($value) <= strtotime($rule);
    }

    /**
     * 验证有效期
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function expire($value, $rule): bool
    {
        if (is_string($rule)) {
            $rule = explode(',', $rule);
        }

        [$start, $end] = $rule;

        if (!is_numeric($start)) {
            $start = strtotime($start);
        }

        if (!is_numeric($end)) {
            $end = strtotime($end);
        }

        return time() >= $start && time() <= $end;
    }

    /**
     * 验证IP许可
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function allowIp($value, $rule): bool
    {
        return in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 验证IP禁用
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则
     * @return bool
     */
    public function denyIp($value, $rule): bool
    {
        return !in_array($value, is_array($rule) ? $rule : explode(',', $rule));
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule 验证规则 正则规则或者预定义正则名
     * @return bool
     */
    public function regex($value, $rule): bool
    {
        if (isset($this->regex[$rule])) {
            $rule = $this->regex[$rule];
        } elseif (isset($this->defaultRegex[$rule])) {
            $rule = $this->defaultRegex[$rule];
        }

        if (is_string($rule) && 0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return is_scalar($value) && 1 === preg_match($rule, (string)$value);
    }

    /**
     * 获取错误信息
     * @return array|string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 获取数据值
     * @access protected
     * @param array $data 数据
     * @param string $key 数据标识 支持二维
     * @return mixed
     */
    protected function getDataValue(array $data, $key)
    {
        if (is_numeric($key)) {
            $value = $key;
        } elseif (is_string($key) && strpos($key, '.')) {
            // 支持多维数组验证
            foreach (explode('.', $key) as $key) {
                if (!isset($data[$key])) {
                    $value = null;
                    break;
                }
                $value = $data = $data[$key];
            }
        } else {
            $value = $data[$key] ?? null;
        }

        return $value;
    }

    /**
     * 获取验证规则的错误提示信息
     * @access protected
     * @param string $attribute 字段英文名
     * @param string $title 字段描述名
     * @param string $type 验证规则名称
     * @param mixed $rule 验证规则数据
     * @return string|array
     */
    protected function getRuleMsg(string $attribute, string $title, string $type, $rule)
    {
        if (isset($this->message[$attribute . '.' . $type])) {
            $msg = $this->message[$attribute . '.' . $type];
        } elseif (isset($this->message[$attribute][$type])) {
            $msg = $this->message[$attribute][$type];
        } elseif (isset($this->message[$attribute])) {
            $msg = $this->message[$attribute];
        } elseif (isset($this->typeMsg[$type])) {
            $msg = $this->typeMsg[$type];
        } elseif (0 === strpos($type, 'require')) {
            $msg = $this->typeMsg['require'];
        } else {
            $msg = $title . '不符合规定';
        }

        if (is_array($msg)) {
            return $this->errorMsgIsArray($msg, $rule, $title);
        }

        return $this->parseErrorMsg($msg, $rule, $title);
    }


    /**
     * 获取验证规则的错误提示信息
     * @access protected
     * @param string $msg 错误信息
     * @param mixed $rule 验证规则数据
     * @param string $title 字段描述名
     * @return string
     */
    protected function parseErrorMsg(string $msg, $rule, string $title)
    {
        if (0 === strpos($msg, '{%')) {
            $msg = $this->lang->get(substr($msg, 2, -1));
        }

        if (is_array($msg)) {
            return $this->errorMsgIsArray($msg, $rule, $title);
        }

        if (is_scalar($rule) && false !== strpos($msg, ':')) {
            // 变量替换
            if (is_string($rule) && strpos($rule, ',')) {
                $array = array_pad(explode(',', $rule), 3, '');
            } else {
                $array = array_pad([], 3, '');
            }

            $msg = str_replace(
                [':attribute', ':1', ':2', ':3'],
                [$title, $array[0], $array[1], $array[2]],
                $msg
            );

            if (strpos($msg, ':rule')) {
                $msg = str_replace(':rule', (string)$rule, $msg);
            }
        }

        return $msg;
    }

    /**
     * 错误信息数组处理
     * @access protected
     * @param array $msg 错误信息
     * @param mixed $rule 验证规则数据
     * @param string $title 字段描述名
     * @return array
     */
    protected function errorMsgIsArray(array $msg, $rule, string $title)
    {
        foreach ($msg as $key => $val) {
            if (is_string($val)) {
                $msg[$key] = $this->parseErrorMsg($val, $rule, $title);
            }
        }
        return $msg;
    }

    /**
     * 获取数据验证的场景
     * @access protected
     * @param string $scene 验证场景
     * @return void
     */
    protected function getScene(string $scene): void
    {
        $this->only = $this->append = $this->remove = [];

        if (method_exists($this, 'scene' . $scene)) {
            call_user_func([$this, 'scene' . $scene]);
        } elseif (isset($this->scene[$scene])) {
            // 如果设置了验证适用场景
            $this->only = $this->scene[$scene];
        }

    }

    public function getSceneRule(string $name)
    {
        return $this->scene[$name] ?? $this->rule;
    }

    function intOrArrayInt($array)
    {
        if (is_numeric($array)) return true;
        foreach ($array as $value) {
            if (!is_numeric($value)) // there are several ways to do this
            {
                return false;
            }
        }
        return true;
    }

    function arrayHasOnlyInts($array)
    {
        foreach ($array as $value) {
            if (!is_numeric($value)) // there are several ways to do this
            {
                return false;
            }
        }
        return true;
    }

    function string($value)
    {
        return is_string($value);
    }

    /**
     * 动态方法 直接调用is方法进行验证
     * @access public
     * @param string $method 方法名
     * @param array $args 调用参数
     * @return bool
     */
    public function __call($method, $args)
    {
        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_push($args, lcfirst($method));

        return call_user_func_array([$this, 'is'], $args);
    }
}

