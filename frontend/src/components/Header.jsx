import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { FaUserCircle } from "react-icons/fa";

export default function Header({ title }) {
  const [user, setUser] = useState(null);
  const navigate = useNavigate();

  useEffect(() => {
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
  }, []);

  const handleProfileClick = () => {
    navigate("/profil"); 
  };

  return (
    <header className="dashboard-header">
      <h1>{title}</h1>
      {user && (
        <div className="header-profile" onClick={handleProfileClick}>
          <FaUserCircle className="profile-icon" />
          <div className="profile-info">
            <span className="profile-name">{user.name_user}</span>
            <span className="profile-role">{user.role}</span>
          </div>
        </div>
      )}
    </header>
  );
}
