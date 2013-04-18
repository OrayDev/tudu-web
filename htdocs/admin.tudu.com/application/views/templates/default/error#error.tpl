<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Error</title>
</head>
<body>
  <h1>An error occurred</h1>
  <h2>{{$message}}</h2>

  {{if $exception}}

  <h3>Exception information:</h3>
  <p>
      <b>Message:</b> {{$exception->getMessage()}}
  </p>

  <h3>Stack trace:</h3>
  <pre>{{$exception->getTraceAsString()}}
  </pre>

  <h3>Request Parameters:</h3>
  <pre>{{var_export _params=$request->getParams()}}
  </pre>
  {{/if}}

</body>
</html>