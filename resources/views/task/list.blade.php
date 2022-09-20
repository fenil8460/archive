@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Task') }}</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Url</th>
                            <th>Status</th>
                            <th>Action</th>
                        <tr>
                        </thead>
                        <tbody>
                        @foreach($tasks as $task)
                        <tr>
                            <td>{{$task->name}}</td>
                            <td>{{$task->url}}</td>
                            <td>
                                @php
                                $status = $task->status == 0 ? 'spam' : 'active';
                                @endphp
                                {{$status}}
                            </td>
                            <td><a href="/url-spanshot?url={{$task->url}}" class="btn btn-primary">SpanShot</a></td>
                        <tr>
                            @endforeach
                        </tbody>
                        
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection