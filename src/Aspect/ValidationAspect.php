<?php

declare(strict_types=1);

namespace Mzh\Validate\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\Exception;
use Hyperf\Utils\Context;
use Mzh\Validate\Annotations\RequestValidation;
use Mzh\Validate\Annotations\Validation;
use Mzh\Validate\Exception\ValidateException;
use Mzh\Validate\Validate\Validate;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @Aspect
 */
class ValidationAspect extends AbstractAspect
{
    protected $container;
    protected $request;

    // 要切入的类，可以多个，亦可通过 :: 标识到具体的某个方法，通过 * 可以模糊匹配
    public $annotations = [
        Validation::class,
        RequestValidation::class
    ];

    public function __construct(ContainerInterface $container, ServerRequestInterface $Request)
    {
        $this->container = $container;
        $this->request = $this->container->get(ServerRequestInterface::class);
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @return mixed
     * @throws Exception
     * @throws ValidateException
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        foreach ($proceedingJoinPoint->getAnnotationMetadata()->method as $validation) {
            /**
             * @var Validation $validation
             */
            switch (true) {
                case $validation instanceof RequestValidation:
                    if (empty($validation->validate)) {
                        if (!empty($validation->mode)) {
                            $class = $validation->mode;
                        } else {
                            $class = class_basename($proceedingJoinPoint->className);
                        }
                        $validation->validate = '\\App\\Validate\\' . $class . 'Validation';
                    }
                    $verData = $this->request->all();
                    $this->validationData($validation, $verData, $validation->validate, $proceedingJoinPoint, true);
                    break;
                case $validation instanceof Validation:
                    if (empty($validation->validate)) {
                        if (!empty($validation->mode)) {
                            $name = $validation->mode;
                        } else {
                            throw new ValidateException('validate和mode 不能同时为空');
                        }
                        $validation->validate = '\\App\\Validate\\' . $name . 'Validation';
                    }
                    $verData = $proceedingJoinPoint->arguments['keys'][$validation->field];
                    $this->validationData($validation, $verData, $validation->validate, $proceedingJoinPoint);
                    break;
                default:
                    break;
            }
        }
        return $proceedingJoinPoint->process();
    }

    /**
     * @param $validation
     * @param $verData
     * @param $class
     * @param $proceedingJoinPoint
     * @param $isRequest
     * @throws ValidateException
     */
    private function validationData($validation, $verData, $class, $proceedingJoinPoint, $isRequest = false)
    {
        /**
         * @var RequestValidation $validation
         */
        /**
         * @var Validate $validate
         */
        if (class_exists($class)) {
            $validate = new $class;
        } else {
            throw new ValidateException('class not exists:' . $class);
        }
        if ($validation->scene == '') {
            $validation->scene = $proceedingJoinPoint->methodName;
        }
        $rules = $validate->getSceneRule($validation->scene);

        if ($validate->batch($validation->batch)->check($verData, $rules) === false) {
            throw new ValidateException($validate->getError());
        }

        if ($validation->security) {
            $fields = [];
            foreach ($rules as $field => $rule) {
                if (is_numeric($field)) {
                    $field = $rule;
                }
                $fields[$field] = 1;
            }
            foreach ($verData as $key => $item) {
                if (!isset($fields[$key])) {
                    throw new ValidateException($key . ' invalid');
                }
            }
        };

        if ($validation->filter) {
            foreach ($rules as $key => $item) {
                if (isset($verData[$key]) && $verData[$key] === null) {
                    unset($verData[$key]);
                }
            }

            switch ($isRequest) {
                case true:
                    Context::override(ServerRequestInterface::class, function (ServerRequestInterface $request) use ($verData) {
                        return $request->withParsedBody($verData);
                    });
                    break;
                default:
                    $proceedingJoinPoint->arguments['keys'][$validation->field] = $verData;
                    break;
            }
        }
    }
}
