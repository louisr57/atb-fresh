<div x-data="{
    open: false,
    search: @entangle('search'),
    items: @entangle('items'),
    filterItems() {
        return this.items.filter(item =>
            item.toLowerCase().includes(this.search.toLowerCase())
        );
    }
}" @click.away="open = false">
    <input type="text" placeholder="Search..." wire:model.debounce.300ms="search" @focus="open = true" x-model="search">

    <ul x-show="open" class="dropdown-list">
        <template x-for="item in filterItems()" :key="item">
            <li @click="$wire.set('selectedItem', item); search = item; open = false" x-text="item"></li>
        </template>
        <li x-show="filterItems().length === 0">No results found</li>
    </ul>
</div>