import React, { useEffect, useState } from "react";
import { v4 as uuidv4 } from "uuid";
import CryptoJS from "crypto-js";

const App = () => {
  const [totalAmount, setTotalAmount] = useState(null);
  const [formData, setFormData] = useState(null);

  const secret = "8gBm/:&EnhH.1/q";
  const transaction_uuid = uuidv4();
  const product_code = "EPAYTEST";

  const generateSignature = (total_amount, uuid, product_code, secret) => {
    const hashString = `total_amount=${total_amount},transaction_uuid=${uuid},product_code=${product_code}`;
    const hash = CryptoJS.HmacSHA256(hashString, secret);
    return CryptoJS.enc.Base64.stringify(hash);
  };

  // Fetch total amount
  useEffect(() => {
    fetch("http://localhost/bisap/cart_total_api.php", {
      credentials: "include", // Required for session
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.total) {
        const total = String(Number(data.total) + 500); // adds 500 to simulate more payment

          setTotalAmount(total);

          const signature = generateSignature(
            total,
            transaction_uuid,
            product_code,
            secret
          );

          setFormData({
            amount: total,
            tax_amount: "0",
            total_amount: total,
            transaction_uuid,
            product_service_charge: "0",
            product_delivery_charge: "0",
            product_code,
            success_url: "http://localhost:5173/paymentsuccess",
            failure_url: "http://localhost:5173/paymentfailure",
            signed_field_names: "total_amount,transaction_uuid,product_code",
            signature,
          });
        } else {
          console.error("Error fetching total:", data.error);
        }
      });
  }, []);

  if (!formData) return <h3>Loading cart total...</h3>;

  return (
    <form
      action="https://rc-epay.esewa.com.np/api/epay/main/v2/form"
      method="POST"
    >
      <h1>Checkout</h1>

      <div className="field">
        <label>Amount</label>
        <input type="text" value={formData.amount} readOnly />
      </div>

      {/* Hidden Fields */}
      {Object.entries(formData).map(([key, value]) => (
        key !== "amount" && key !== "secret" && (
          <input key={key} type="hidden" name={key} value={value} />
        )
      ))}

      <input className="btn" value="Pay via E-Sewa" type="submit" />
    </form>
  );
};

export default App;
