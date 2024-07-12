<div class="flex items-center py-4 space-x-4 ">
    <a href="#">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            Add new episode
        </x-button>
    </a>
    <a href="{{ route('frontend.podcasts.show', $podcast) }}">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            Go to public page
        </x-button>
    </a>
    <a href="#">
        <x-button class="bg-blue-600 hover:bg-blue-700">
            Statistics
        </x-button>
    </a>
    @if($podcast->episodes->count()> 0 )
        <a href="#">
            <x-button class="bg-green-600 hover:bg-green-700">
                Edit metadata of multiple episodes
            </x-button>
        </a>
        <a href="#">
            <x-button class="bg-green-600 hover:bg-green-700">
                Reorder podcast episodes
            </x-button>
        </a>
    @endif
    @can('edit-podcast', $podcast)
        <x-modals.delete :route="route('podcasts.destroy', $podcast)">
            <x-slot:title>
                {{__('series.backend.delete.modal title',['series_title'=>$podcast->title])}}
            </x-slot:title>
            <x-slot:body>
                {{__('series.backend.delete.modal body')}}
            </x-slot:body>
        </x-modals.delete>
    @endcan
</div>
