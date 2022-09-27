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
                <div class="card-header">{{ __('SnapShot') }}<a href="{{ URL::previous() }}" class="btn btn-primary list-task">View URLs</a></div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Url</th>
                                <th>Status</th>
                                <th>Reason</th>
                                <th>Date</th>
                            <tr>
                        </thead>
                        <tbody>
                        @foreach($snapshots as $snapshot)
                        <tr>
                            <td><a href="{{$snapshot['snapshot']}}" target="_blank">{{$snapshot['snapshot']}}</a></td>
                            <td>{{$snapshot['status_name']}}</td>
                            <td>{{$snapshot['reason'] == null ? '--' : $snapshot['reason']}}</td>
                            <td>{{date('Y-m-d H:i:s', strtotime($snapshot['timestamp']))}}</td>
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