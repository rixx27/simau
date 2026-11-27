import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';

window.ensureChartJs = async () => {
    if (!window.Chart) {
        const { default: Chart } = await import('chart.js/auto');
        window.Chart = Chart;
    }

    return window.Chart;
};

window.Livewire = Livewire;
window.Alpine = Alpine;
Livewire.start();
