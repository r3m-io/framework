{{R3M}}
{{$is.set  = dir.set(config('project.dir.root'))}}
{{core.exec('doctrine orm:generate-proxies', 'output')}}
{{dd('{{$this}}')}}
{{$output)}}
{{$output_error)}}

{{core.exec('chown www-data:www-data "/tmp" -R', 'output')}}
{{$output)}}


