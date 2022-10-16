import React from "react";
import styled from "styled-components";
import { PurpleButton, WhiteButton } from "../../styles/formularios";

const DivAlerta = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  text-align: center;
  gap: 10px;
`;

const DivAlertaText = styled.div`
  display: flex;
  flex-direction: column;
  height: 250px;
  justify-content: space-around;
`;

const DivIcon = styled.i`
  font-size: 100px;
  color: #660BE1;
`;

const H2Title = styled.h2`
  font-size: 1.2rem;
  color: #1a3260;
`;

const PText = styled.p`
  font-size: 1rem;
  color: #808291;
`;

const DivButtons = styled.div`
  display: flex;
  justify-content: center;
  gap: 20px;
`;

const ConfirmModal = ({ cerrar, sure, text }) => {
  return (
    <DivAlerta>
      <DivAlertaText>
        <DivIcon className='fa-solid fa-circle-exclamation'></DivIcon>
        <H2Title>¿Estás seguro?</H2Title>
        <PText>{text}.</PText>
      </DivAlertaText>
      <DivButtons>
        <WhiteButton onClick={cerrar}>Cerrar</WhiteButton>
        <PurpleButton onClick={() => {sure(); cerrar();}}>Enviar</PurpleButton>
      </DivButtons>
    </DivAlerta>
  )
}

export default ConfirmModal;