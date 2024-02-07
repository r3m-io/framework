{{R3M}}
<html>
<head>
    <title>HTTP/1.0 500 Internal server error: {{$exception.file}}</title>
    <style>
        {{require(config('framework.dir.view') + 'Http/Head/Style.rcss')}}
    </style>
</head>
<body>
<section name="header">
    <h3>HTTP/1.0 500 Internal server error:</h3>
</section><section name="message">
    <h3>{{$exception.message}}</h3>
</section><section name="detail">
    <label>Exception: </label>
    <span>{{$exception.className}}</span><br>
    <label>File: </label>
    <span>{{$exception.file}}</span><br>
    <label>Line: </label>
    <span>{{$exception.line}}</span><br>
    <label>Code: </label>
    <span>{{$exception.code}}</span><br>
</section>
{{if(
config('framework.environment') === 'development' &&
!is.empty($exception.file)
)}}
<section name="source">
    <label>Source: </label><br>
    {{$source = file.read($exception.file)}}
    {{if($source)}}
    {{$read = explode("\n", $source)}}
    <table>
        {{for($i=$exception.line - 3 - 1; $i <= $exception.line + 3 - 1; $i++)}}
        {{$row = $read[$i]}}
        {{$row_nr = $i + 1}}
        {{if(
        $i === $exception.line - 1 &&
        is.set($row)
        )}}
        <tr class="selected"><td class="line"><pre>{{$row_nr}}</pre></td><td class="row"><pre>{{$row}}</pre></td></tr>
        {{elseif(is.set($row))}}
        <tr><td class="line"><pre>{{$row_nr}}</pre></td><td class="row"><pre>{{$row}}</pre></td></tr>
        {{/if}}
        {{/for}}
    </table>
    {{/if}}
</section>
{{/if}}
{{if(
config('framework.environment') === 'development' &&
!is.empty($exception.trace)
)}}
<section name="trace">
    <label>Trace: </label><br>
    <table>
        {{for.each($exception.trace as $nr => $trace)}}
        <tr class="trace">
            <td class="tab">&nbsp;</td>
            <td class="title">{{$trace.file}} ({{$trace.line}})</td>
            <td class="function">{{$trace.function}}</td>
            <td class="class">{{$trace.class}}</td>
        </tr>
        <tr class="trace-source">
            <td colspan="4">
                <label>Source: </label><br>
                {{$source = file.read($trace.file)}}
                {{if($source)}}
                {{$read = explode("\n", $source)}}
                <table>
                    {{for($i=$trace.line - 3 - 1; $i <= $trace.line + 3 - 1; $i++)}}
                    {{$row = $read[$i]}}
                    {{$row_nr = $i + 1}}
                    {{if(
                    $i === $trace.line - 1 &&
                    is.set($row)
                    )}}
                    <tr class="selected"><td class="line"><pre>{{$row_nr}}</pre></td><td class="row"><pre>{{$row}}</pre></td></tr>
                    {{elseif(is.set($row))}}
                    <tr><td class="line"><pre>{{$row_nr}}</pre></td><td class="row"><pre>{{$row}}</pre></td></tr>
                    {{/if}}
                    {{/for}}
                </table>
                {{/if}}
            </td>
        </tr>
        {{/for.each}}
    </table>
</section>
{{/if}}
</body>
</html>