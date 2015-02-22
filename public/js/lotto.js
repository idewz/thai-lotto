Polymer('app-element', {
    ready: function() {
        this.setResult(window.initialData);
    },
    setResult: function(result) {
        this.selected_date = result.date;
        this.prizes = this.mapPrizes(result.prizes);
    },
    mapPrizes: function(prizes) {
        return [
            { label: 'รางวัลที่หนึ่ง', data: prizes.first },
            { label: 'เลขท้ายสองตัว', data: prizes.last_two_digits },
            { label: 'เลขท้ายสามตัว', data: prizes.last_three_digits },
            { label: 'รางวัลที่สอง',  data: prizes.second },
            { label: 'รางวัลที่สาม',  data: prizes.third },
            { label: 'รางวัลที่สี่',   data: prizes.fourth },
            { label: 'รางวัลที่ห้า',   data: prizes.fifth },
        ];
    },
    updateSelectedUrl: function(e, detail, sender) {
        this.selected_url = '/api/result/' + e.target.templateInstance.model.date;
    },
    updateResult: function(e, detail, sender) {
        this.setResult(detail.response);
    },
});