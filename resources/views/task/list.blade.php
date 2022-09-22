@extends('layouts.app')

@section('content')
<style>
    a.btn.btn-primary.list-task {
        float: right;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
            <div class="card-header">{{ __('Task') }} <a href="/task" class="btn btn-primary list-task">Create Task</a></div>
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
                            <td>{{$task->status_name}}</td>
                            <td><a href="/url-spanshot?url={{$task->url}}" class="btn btn-primary">SpanShot</a></td>
                        <tr>
                            @endforeach
                        </tbody>
                        
                    </table>
                    {{ $tasks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection