import { useState } from "react";
import axios from "axios";

export default function Login() {
  return (
    <div style={{ padding: 40 }}>
      <h2>Login Admin</h2>

      <input placeholder="Email" />
      <br /><br />

      <input type="password" placeholder="Password" />
      <br /><br />

      <button>Login</button>
    </div>
  );
}
