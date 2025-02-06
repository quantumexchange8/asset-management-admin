export function generalFormat() {
    function formatDateTime(date, includeTime = true) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const formattedDate = new Date(date);

        const day = formattedDate.getDate().toString().padStart(2, '0');
        const month = months[formattedDate.getMonth()];
        const year = formattedDate.getFullYear();
        const hours = formattedDate.getHours().toString().padStart(2, '0');
        const minutes = formattedDate.getMinutes().toString().padStart(2, '0');
        const seconds = formattedDate.getSeconds().toString().padStart(2, '0');

        if (includeTime) {
            return `${day} ${month} ${year} ${hours}:${minutes}:${seconds}`;
        } else {
            return `${day} ${month} ${year}`;
        }
    }

    function formatAmount(amount, decimalPlaces = 2) {
        const formattedAmount = parseFloat(amount).toFixed(decimalPlaces);
        const integerPart = formattedAmount.split('.')[0];
        const decimalPart = formattedAmount.split('.')[1];
        const integerWithCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return decimalPlaces > 0 ? `${integerWithCommas}.${decimalPart}` : integerWithCommas;
    }

    const formatRgbaColor = (hex, opacity) => {
        const r = parseInt(hex.slice(0, 2), 16);
        const g = parseInt(hex.slice(2, 4), 16);
        const b = parseInt(hex.slice(4, 6), 16);
        return `rgba(${r}, ${g}, ${b}, ${opacity})`;
    };

    return {
        formatDateTime,
        formatAmount,
        formatRgbaColor
    };
}
