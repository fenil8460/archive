@extends('layouts.app')

@section('content')
<style>
    a.btn.btn-primary.list-task {
        float: right;
    }

    .import-form {
        display: flex;
        margin-left: 150px;
        width: 60%;
    }

    .url_list {
        display: flex;
        /* justify-content: space-between; */
    }

    input.form-control.col-6.files {
        margin-right: 13px;
    }

    a.btn.btn-success.export-data {
        margin-bottom: 16px;
    }

    a.btn.btn-success.sample-data {
        margin-right: 10px;
    }
</style>
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    @if(session()->has('message'))
    <div class="alert alert-success">
        {{ session()->get('message') }}
    </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header url_list">
                    <span>
                        {{ __('URls') }}
                    </span>
                    <form action="{{ route('import') }}" class="import-form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="file" class="form-control col-6 files">
                        <input type="hidden" name="task_id" value="{{$task_id}}" class="form-control">
                        <br>
                        <button type="submit" class="btn btn-success">
                            Import
                        </button>
                    </form>
                    <a href="{{url('sample-export')}}" class="btn btn-success sample-data">Sample File</a>
                    <a href="/list-task" class="btn btn-primary list-task">View Task</a>
                </div>


                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="active-tab" data-toggle="tab" href="#active" role="tab" aria-controls="active" aria-selected="true">Active</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="spam-tab" data-toggle="tab" href="#spam" role="tab" aria-controls="spam" aria-selected="false">Spam</a>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade  show active" id="active" role="tabpanel" aria-labelledby="active-tab">
                        <div class="card-body">
                            {{$task_name->name}}
                            <a href="{{url('export')}}/{{$task_id}}/active" class="btn btn-success export-data" style="margin-left:85%">Export</a>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>URL</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    <tr>
                                </thead>
                                <tbody>
                                    @foreach($url_actives as $url)
                                    <tr>
                                        <td>{{$url->url}}</td>
                                        <td>Ok</td>
                                        <td><a href="/url-spanshot?url={{$url->url}}" class="btn btn-primary">SnapShot</a></td>
                                    <tr>
                                        @endforeach
                                </tbody>

                            </table>
                            {{ $url_actives->links() }}
                        </div>
                    </div>
                    <div class="tab-pane fade" id="spam" role="tabpanel" aria-labelledby="spam-tab">
                        <div class="card-body">
                            {{$task_name->name}}
                            <a href="{{url('export')}}/{{$task_id}}/spam" class="btn btn-success export-data" style="margin-left:85%">Export</a>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>URL</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th>Action</th>
                                    <tr>
                                </thead>
                                <tbody>
                                    @foreach($url_spams as $url)
                                    <tr>
                                        <td>{{$url->url}}</td>
                                        <td>{{$url->status_name}}</td>
                                        <td>{{$url->reason == null ? '--' : $url->reason}}</td>
                                        <td><a href="/url-spanshot?url={{$url->url}}" class="btn btn-primary">SpanShot</a></td>
                                    <tr>
                                        @endforeach
                                </tbody>

                            </table>
                            {{ $url_spams->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection