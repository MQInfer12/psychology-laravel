import { async } from "@firebase/util";
import {
  collection,
  doc,
  getDocs,
  onSnapshot,
  query,
  where,
} from "firebase/firestore";
import React, { useContext, useEffect, useState } from "react";
import styled from "styled-components";
import { ChatContext } from "../../context/chatContext";
import { UserFirebaseContext } from "../../context/userFirebaseContext";
import { db } from "../../firebase";
import ProfilePic from "../globals/profilePic";

const Container = styled.div`
  .userChat {
    padding: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    cursor: pointer;
    width: 100%;

    &>div:first-child{
      min-width: 50px;
    }
    &:hover {
      background-color: #2f2d52;
    }

    .userChatInfo {
      span {
        font-size: 18px;
        font-weight: 500;
      }
      p {
        font-size: 14px;
        color: lightgray;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    }
  }


`;

const Chats = ({ handleClick }) => {
  const [chats, setChats] = useState([]);
  const { currentUser } = useContext(UserFirebaseContext);
  const { dispatch } = useContext(ChatContext);

  const getUserByChat = async (data) => {
    let chatsData = [];
    for (let key in data) {
      const userChat = data[key];
      const userInfo = userChat.userInfo;
      const uid = userInfo.uid;

      const q = query(collection(db, "users"), where("uid", "==", Number(uid)));
      try {
        const querySnapshot = await getDocs(q);
        const { email, perfil } = querySnapshot.docs[0].data();
        userChat.userInfo.email = email;
        userChat.userInfo.perfil = perfil;
        chatsData.push(userChat);
      } catch (err) {
        console.log(err);
      }
    }
    return chatsData;
  };

  useEffect(() => {
    const getChats = async () => {
      const unsub = onSnapshot(
        doc(db, "userChats", currentUser?.uid),
        async (doc) => {
          const data = doc.data();
          const chatsData = await getUserByChat(data);
          chatsData.sort((a, b) => b.date - a.date);
          setChats(chatsData);
        }
      );
      return () => {
        unsub();
      };
    };

    currentUser?.uid && getChats();
  }, [currentUser?.uid]);

  const handleSelect = (u) => {
    dispatch({ type: "CHANGE_USER", payload: u });
  };

  const handleShowChat = (v) => {
    handleSelect(v.userInfo);
    if(window.innerWidth <= 1080) {
      handleClick();
    }
  }

  return (
    <Container>
      {chats &&
        chats.map((v, i) => (
          <div
            className="userChat"
            key={i}
            onClick={() => {
              handleShowChat(v);
            }}
          >
            <ProfilePic
              width="50px"
              height="50px"
              id={v.userInfo.uid}
              perfil={v.userInfo.perfil}
              className="img"
            />
            <div className="userChatInfo">
              <span>{v.userInfo.email}</span>
              <p>{v.lastMessage != undefined && v.lastMessage.text}</p>
            </div>
          </div>
        ))}
    </Container>
  );
};

export default Chats;