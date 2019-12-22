# hyperf-validate
支持request 和方法场景验证，兼容tp5写法
性能比官方验证器提高50% 


注解
     @Validation
     @RequestValidation

例如 验证控制器Admin 的login方法传的数据，需要如下操作

```
use Mzh\Validate\Annotations\RequestValidation;

mode="Admin" 验证的模块规则/app/Validate/AdminValidation.php 文件的验证规则
filter=true 过滤掉规则外无用参数 过滤后会重新写入  $this->request->getParsedBody()内，需要时直接取，数据是安全的，验证过的
throw=true 严格验证模式，如果开启，则用户传入无用参数，直接抛出异常，提示传入的字段xx无效，


/**
 * @RequestValidation(mode="Admin",filter=true,throw=true
 )
 */
public function login(){
     $data = $this->request->getParsedBody();


}



```

