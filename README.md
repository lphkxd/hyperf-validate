# hyperf-validate
支持request 和方法场景验证，兼容tp5写法
性能比官方验证器提高50% 

<p align="center">
    <a href="https://github.com/lphkxd/hyperf-validate/releases"><img src="https://poser.pugx.org/mzh/hyperf-validate/v/stable" alt="Stable Version"></a>
    <a href="https://travis-ci.org/mzh/hyperf-validate"><img src="https://travis-ci.org/mzh/hyperf-validate.svg?branch=master" alt="Build Status"></a>
    <a href="https://packagist.org/packages/mzh/hyperf-validate"><img src="https://poser.pugx.org/mzh/hyperf-validate/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/mzh/hyperf-validate"><img src="https://poser.pugx.org/mzh/hyperf-validate/d/monthly" alt="Monthly Downloads"></a>
    <a href="https://www.php.net"><img src="https://img.shields.io/badge/php-%3E=7.1-brightgreen.svg?maxAge=2592000" alt="Php Version"></a>
    <a href="https://github.com/swoole/swoole-src"><img src="https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000" alt="Swoole Version"></a>
    <a href="https://github.com/lphkxd/hyperf-validate/blob/master/LICENSE"><img src="https://img.shields.io/github/license/lphkxd/hyperf-validate.svg?maxAge=2592000" alt=" License"></a>
</p>



## 安装方法

     安装方法  composer require mzh/hyperf-validate
     

注：使用验证类方法的注解@Validation 需要用在由DI创建的类才有作用，
## 具体使用方法可以参考项目 https://github.com/lphkxd/hyperf-admin

## 注解
     @Validation
     @RequestValidation

### @RequestValidation 参数说明
```
mode="Admin" 默认取当前类文件名 验证的模块规则/app/Validate/AdminValidation.php 文件的验证规则
scene="场景" 场景，验证哪个场景。默认不写为默认的验证规则
filter=true 过滤掉规则外无用参数 过滤后会重新写入$this->request->getParsedBody()内，
              需要时直接取，数据是安全的，验证过的
throw=true 严格验证模式，如果开启，则用户传入无用参数，直接抛出异常，提示传入的字段xx无效，
```
### @Validation 参数说明
```
mode="Admin" 验证的模块规则/app/Validate/AdminValidation.php 文件的验证规则
scene="场景" 场景，验证哪个场景。默认不写为默认的验证规则
filter=true 过滤掉规则外无用参数 过滤后会重新写入对应的字段内，需要时直接取，数据是安全的，验证过的
throw=true 严格验证模式，如果开启，则用户传入无用参数，直接抛出异常，提示传入的字段xx无效，
field="data" 方法的参数名，例如 function($data,$array,$array3) 需要验证这个方法的$array参数，这里填array
```  
    
## 验证控制器数据方法如下


### 例如 验证控制器Admin 的login方法传的数据，需要如下操作
```
use Mzh\Validate\Annotations\RequestValidation;

/**
 * @RequestValidation(filter=true,throw=true)
 */
public function login(){
      //这里取到的 $data 是安全的。
     $data = $this->request->getParsedBody();
}
```

## 验证类方法数据方法如下
例如 验证AdminService类的login方法传的数据，需要如下操作
```
use Mzh\Validate\Annotations\Validation;

/**
 * @Validation(mode="Admin",scene="login",field="data")
 * @Validation(mode="Admin",scene="array的规则",field="array")
 * @Validation(mode="Admin",scene="array2的规则",field="array2")
 */
public function login($data,$array,$array2){
      //这里取到的 $data,$array,$array2 是安全的，经过验证器验证过的

}
```
