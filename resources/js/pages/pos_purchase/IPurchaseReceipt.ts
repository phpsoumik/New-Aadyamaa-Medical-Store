export interface ItemList {
    mode: string,
    stockID: number,
    productID: number,
    productName: string,
    generic: string,
    itemDescription: string,
    leaf:number,
    unit: number,
    totalUnit: number,
    stockQty: number,
    freeUnit: number,
    supplierBonus: number,
    batchNo: string,
    packSize: number,
    sheetSize: number,
    purchasePrice: number,
    sellingPrice: number,
    orginalSPrice: number,
    mrp: number,
    brandName: string,
    sectorName: string,
    categoryName: string,
    productType: string,
    expiryDate: string,
    itemDisc: number,
    specialDisc: number;
    cusDisc: number,
    purchaseAfterDisc: number,
    tax1: number,
    tax2: number,
    tax3: number,
    subTotal: number,
    preturn:number,
    isSelected:number,
    hsn: number,
    barcode: string
}

export interface CounterEntry {
    accountID: number;
    accountHead: string;
    amount: number;
    type: string;
  }

  export  interface PaymentListType {
    paymentType: string;
    accountNo: string;
    terminalId: string;
    authCode: string;
    transId: string;
    transStatus: string;
    transType: string;
    transDate: string;
    transTime: string;
    transAmount: number;
    transTotalAmount: number;
    transRef: string;
    entryMode: string;
    hostResponse: string;
    giftCardRef: string;
    cardBalance: string;
    tendered: number;
    change: number;
    roundOff: number;
  }