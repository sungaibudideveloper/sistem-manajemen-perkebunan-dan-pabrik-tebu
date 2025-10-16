<style>
    select:invalid {
        color: gray;
    }
</style>
<style>
    .table-container {
        display: grid;
        justify-content: justify;
        align-items: flex-start;
        place-items: center;
        min-height: 10vh;
        padding: 0px;
        overflow-x: auto;
    }

    table#listTable input[type="text"],
    table#listTable input[type="number"] {
        width: 80px;
        padding: 4px;
        font-size: 12px;
    }

    table#listTable th,
    table#listTable td {
        text-align: center;
        padding: 2px;
    }

    #listTable {
        table-layout: fixed;
    }

    input[disabled] {
        background-color: #f0f0f0;
        cursor: not-allowed;
    }
</style>
<style>
    #scrollToTop {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }

    #scrollToTop button {
        padding: 18px 20px;
        background-color: #a1a1a1;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        opacity: 50%;
    }

    #scrollToTop button:hover {
        background-color: #747474;
    }
</style>
