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
                                <th>Date</th>
                            <tr>
                        </thead>
                        <tbody>
                        @foreach($snapshots as $snapshot)
                        <tr>
                            <td><a href="{{$snapshot['url']}}" target="_blank">{{$snapshot['url']}}</a></td>
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