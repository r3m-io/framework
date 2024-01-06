{{R3M}}
<html>
<head>
    <title>HTTP/1.0 501 Not Implemented: {{$exception.file}}</title>
    <style>
        {{require(config('framework.dir.view') + 'Http/Head/Style.rcss')}}
        /*
        body, html, section {
            margin: 0;
            padding: 0;
        }
        section[name="header"] {
            background: rgba(200, 0, 0, 1);
            width: 100%;
        }
        section[name="header"] h3 {
            display: inline-block;
            padding: 0;
            margin: 10px;
            color: rgba(255, 255, 255, 1);
        }
        section[name="message"] {
            background: rgba(225, 225, 225, 1);
            width: 100%;
        }
        section[name="message"] h3 {
            display: inline-block;
            padding: 0;
            margin: 10px;
            color: rgba(0, 0, 0, 1);
        }
        label {
            font-weight: bold;
            margin-left: 10px;
        }
        table {
            width: 100%:
            margin-left: 10px;
        }
        tr.trace td {
            padding: 10px;
        }
        tr.selected {
            background: rgba(225, 225, 225, 1);
            font-weight: bold;
        }
        td.line {
            background: rgba(235, 235, 235, 1);
        }
        tr.selected td.line {
            font-weight: bold;
        }
        td.tab {
            width: 20px;
        }
        td.function {
            font-weight: bold;
        }
        td.class {
            font-weight: bold;
        }
         */
    </style>
</head>
<body>
<section name="header">
    <h3>HTTP/1.0 501 Not Implemented:</h3>
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
</section><section name="source">
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
</section><section name="trace">
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
</body>
</html>