@extends('layouts.app')

@section('content')
<style>
    a.btn.btn-primary.list-task {
        float: right;
    }
</style>
<div class="container">
    @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Task') }} <a href="/task" class="btn btn-primary list-task">Create Task</a></div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Total domains</th>
                                <th>Created At</th>
                            <tr>
                        </thead>
                        <tbody>
                            @foreach($tasks as $task)
                            <tr>
                                <td><a href="/list-url/{{$task->id}}">{{$task->name}}</a></td>
                                <td>{{$task->count}}</td>
                                <td>{{$task->created_at}}</td>
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