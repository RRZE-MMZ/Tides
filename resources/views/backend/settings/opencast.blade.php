@extends('layouts.backend')

@section('content')
    <div class="flex pb-2 font-semibold border-b border-black text-2xl">
        Opencast settings
    </div>
    <div class="flex py-2 px-2">
        <form action="{{ route('settings.opencast.update') }}"
              method="POST"
              class="w-4/5 ">
            @csrf
            @method('PUT')

            <x-form.input field-name="url"
                          input-type="url"
                          :value="$setting['url']"
                          label="Admin URL"
                          :fullCol="true"
                          :required="true"/>
            <x-form.input field-name="username"
                          input-type="text"
                          :value="$setting['username']"
                          label="Admin username"
                          :fullCol="true"
                          :required="true"/>
            <x-form.input field-name="password"
                          input-type="password"
                          :value="$setting['password']"
                          label="Admin password"
                          :fullCol="true"
                          :required="true"/>
            <x-form.input field-name="default_workflow_id"
                          input-type="text"
                          :value="$setting['default_workflow_id']"
                          label="Default workflow ID"
                          :fullCol="true"
                          :required="true"/>
            <x-form.input field-name="upload_workflow_id"
                          input-type="text"
                          :value="$setting['upload_workflow_id']"
                          label="Upload workflow ID"
                          :fullCol="true"
                          :required="true"/>
            <x-form.input field-name="theme_id_top_right"
                          input-type="number"
                          :value="$setting['theme_id_top_right']"
                          label="Theme ID top right"
                          :fullCol="false"
                          :required="true"/>
            <x-form.input field-name="theme_id_top_left"
                          input-type="number"
                          :value="$setting['theme_id_top_left']"
                          label="Theme ID top left"
                          :fullCol="false"
                          :required="true"/>
            <x-form.input field-name="theme_id_bottom_right"
                          input-type="number"
                          :value="$setting['theme_id_bottom_right']"
                          label="Theme ID bottom right"
                          :fullCol="false"
                          :required="true"/>
            <x-form.input field-name="theme_id_bottom_left"
                          input-type="number"
                          :value="$setting['theme_id_bottom_left']"
                          label="Theme ID top right"
                          :fullCol="false"
                          :required="true"/>
            <div class="py-4 pb-2 border-b-2 border-black text-xl mb-5">
                Archive Settings
            </div>
            <x-form.input field-name="archive_path"
                          input-type="text"
                          :value="$setting['archive_path']"
                          label="Archive path"
                          :fullCol="true"
                          :required="true"/>
            <div class="mt-10">
                <x-button class="bg-blue-600 hover:bg-blue-700">
                    Update
                </x-button>
                <a href="{{ route('settings.portal.index') }}">
                    <x-button type="button" class="bg-gray-600 hover:bg-gray-700">
                        Cancel
                    </x-button>
                </a>
            </div>

        </form>
    </div>
@endsection