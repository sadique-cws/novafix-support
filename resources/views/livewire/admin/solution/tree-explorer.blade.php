<div class="mx-auto mt-5 bg-white rounded-lg shadow-md overflow-hidden">
    <livewire:admin.components.navigation />

    <div class="flex items-center justify-between px-4 py-3 bg-[#1E40AF] text-[#F9FAFB]">
        <div>
            <h3 class="text-xl font-medium">Tree Explorer</h3>
            <p class="text-sm text-blue-100">Explore full diagnosis trees and clone/reuse sub-flows.</p>
        </div>
        <button wire:click="loadTree"
            class="px-3 py-1 bg-white text-blue-600 rounded-md text-sm font-medium hover:bg-blue-50 transition-colors">
            Refresh
        </button>
    </div>

    @if (session()->has('message'))
        <div class="m-4 rounded-md bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="m-4 rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 border-b">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Device</label>
            <select wire:model.change="selectedDevice"
                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 {{ $selectedDevice ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                {{ $selectedDevice ? 'disabled' : '' }}>
                <option value="">Choose Device</option>
                @foreach ($devices as $device)
                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Brand</label>
            <select wire:model.change="selectedBrand"
                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 {{ $selectedBrand ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                {{ $selectedBrand ? 'disabled' : '' }}>
                <option value="">Choose Brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Model</label>
            <select wire:model.change="selectedModel"
                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 {{ $selectedModel ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                {{ $selectedModel ? 'disabled' : '' }}>
                <option value="">Choose Model</option>
                @foreach ($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Problem</label>
            <select wire:model.change="selectedProblem"
                class="w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 {{ $selectedProblem ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                {{ $selectedProblem ? 'disabled' : '' }}>
                <option value="">Choose Problem</option>
                @foreach ($problems as $problem)
                    <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="p-4 grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 rounded-lg border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-800">
                    Tree Viewer
                    @if($tree['problem'])
                        <span class="text-gray-500 font-normal">— {{ $tree['problem']['name'] }}</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500">
                    {{ $tree['count'] }} questions
                </div>
            </div>

            @if($tree['count'] === 0)
                <div class="mt-4 text-sm text-gray-600">Select a problem to load its tree.</div>
            @else
                @if(count($tree['roots']) > 1)
                    <div class="mt-3 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                        Multiple roots detected ({{ count($tree['roots']) }}). Flow may be disconnected.
                    </div>
                @endif

                <div class="mt-4 max-h-[70vh] overflow-auto pr-2"
                    x-data="treeExplorer(@js($tree))">
                    <template x-for="rootId in tree.roots" :key="rootId">
                        <div class="mt-2">
                            <template x-if="tree.nodes[rootId]">
                                <div x-html="renderNode(rootId, 0)"></div>
                            </template>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="text-sm font-semibold text-gray-800">Clone / Reuse Sub-flow</div>
            <div class="mt-1 text-xs text-gray-600">Search by id or question text to find nodes quickly.</div>

            <div class="mt-3">
                <input type="text" wire:model.debounce.300ms="cloneSearch"
                    class="w-full rounded-md border border-gray-300 p-2 text-sm"
                    placeholder="Search questions (id or text)…" />
            </div>

            <div class="mt-4 space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Problem</label>
                    <select wire:model.change="sourceProblemId" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                        <option value="">Choose source problem</option>
                        @foreach($allProblems as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source Start Question</label>
                    <select wire:model.change="sourceQuestionId" class="w-full p-2 border border-gray-300 rounded-md text-sm" @disabled(!$sourceProblemId)>
                        <option value="">Choose question</option>
                        @foreach($this->filteredSourceQuestions as $q)
                            <option value="{{ $q->id }}">#{{ $q->id }} — {{ \Illuminate\Support\Str::limit($q->question_text, 80) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attach Mode</label>
                    <select wire:model.change="attachMode" class="w-full p-2 border border-gray-300 rounded-md text-sm">
                        <option value="yes">Attach to YES</option>
                        <option value="no">Attach to NO</option>
                        <option value="root">Set as ROOT (only if empty)</option>
                    </select>
                </div>

                @if($attachMode !== 'root')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Attach To (Target Question)</label>
                        <select wire:model.change="targetAttachQuestionId" class="w-full p-2 border border-gray-300 rounded-md text-sm" @disabled(!$selectedProblem)>
                            <option value="">Choose target question</option>
                            @foreach($this->filteredTargetQuestions as $q)
                                <option value="{{ $q->id }}">#{{ $q->id }} — {{ \Illuminate\Support\Str::limit($q->question_text, 80) }}</option>
                            @endforeach
                        </select>
                        <div class="mt-1 text-xs text-gray-500">Choose where the cloned sub-flow should continue.</div>
                    </div>
                @else
                    <div class="rounded-md border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600">
                        ROOT attach works only if the target problem has no questions.
                    </div>
                @endif

                <button wire:click="cloneFlow"
                    class="w-full mt-2 px-4 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50"
                    @disabled(!$sourceProblemId || !$sourceQuestionId || !$selectedProblem)>
                    Clone & Attach
                </button>
            </div>
        </div>
    </div>

    <script>
        function treeExplorer(tree) {
            return {
                tree,
                expanded: {},
                toggle(id) {
                    this.expanded[id] = !this.expanded[id];
                    this.$nextTick(() => {});
                },
                isExpanded(id, depth) {
                    if (this.expanded[id] === undefined) return depth < 2;
                    return this.expanded[id];
                },
                esc(s) {
                    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                },
                renderNode(id, depth) {
                    const n = this.tree.nodes[id];
                    if (!n) return '';
                    const hasChildren = !!(n.yes || n.no);
                    const expanded = this.isExpanded(id, depth);
                    const indent = depth * 12;
                    const btn = hasChildren
                        ? `<button type="button" class="h-7 w-7 rounded border border-gray-300 text-gray-700 hover:bg-gray-50" @click.stop="toggle(${id})">${expanded ? '−' : '+'}</button>`
                        : `<span class="inline-block h-7 w-7"></span>`;
                    const card = `
                        <div class="mt-1.5" style="margin-left:${indent}px">
                            <div class="flex items-start gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-2">
                                ${btn}
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-baseline gap-2">
                                        <div class="text-[11px] font-semibold text-gray-500">#${n.id}</div>
                                        <div class="min-w-0 flex-1 text-sm font-semibold text-gray-900 truncate">${this.esc(n.text)}</div>
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-1.5 text-[11px]">
                                        <span class="rounded bg-green-50 px-2 py-1 text-green-700">Y → ${n.yes ?? 'END'}</span>
                                        <span class="rounded bg-red-50 px-2 py-1 text-red-700">N → ${n.no ?? 'END'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    if (!hasChildren || !expanded) return card;

                    let kids = '';
                    if (n.yes) kids += this.renderNode(n.yes, depth + 1);
                    if (n.no) kids += this.renderNode(n.no, depth + 1);
                    return card + kids;
                },
            }
        }
    </script>
</div>

