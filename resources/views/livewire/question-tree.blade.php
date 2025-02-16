<li class="relative">
    <div class="tree-node">
        <span class="question-text">{{ $node['text'] }}</span>
    </div>

    @if($node['yes'] || $node['no'])
        <ul class="ml-6 border-l border-gray-300 pl-4">
            @if($node['yes'])
                <li class="relative">
                    <span class="tree-branch">✔ Yes →</span>
                    <ul class="ml-4">
                        @include('livewire.question-tree', ['node' => $node['yes']])
                    </ul>
                </li>
            @endif
            @if($node['no'])
                <li class="relative">
                    <span class="tree-branch">✖ No →</span>
                    <ul class="ml-4">
                        @include('livewire.question-tree', ['node' => $node['no']])
                    </ul>
                </li>
            @endif
        </ul>
    @endif
</li>
