import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { ModalTextField } from '../../ModalTextFields'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { DatePicker } from '../../../brackets/shared/components/DatePicker'
import { BracketData } from './BracketData'
import { loadBracketData } from '../../loadBracketData'

export const EditBracketModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
  setBracketData: (data: BracketData) => void
}) => {
  const [loading, setLoading] = useState(false)
  const [titleHasError, setTitleHasError] = useState(false)
  const [dateHasError, setDateHasError] = useState(false)

  addClickHandlers({
    buttonClassName: 'wpbb-edit-bracket-button',
    onButtonClick: (b) => {
      loadBracketData(b, props.setBracketData)
      props.setShow(true)
    },
  })

  const onDateError = (error: string) => {
    setDateHasError(true)
  }

  const onDateErrorCleared = () => {
    setDateHasError(false)
  }

  const onEditBracket = () => {
    if (!props.bracketData.title) {
      setTitleHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateBracket(props.bracketData.id, {
        title: props.bracketData.title,
        month: props.bracketData.month,
        year: props.bracketData.year,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
      })
      .finally(() => {
        props.setShow(false)
      })
  }
  return (
    <Modal show={props.show} setShow={props.setShow}>
      <div className="tw-flex tw-flex-col">
        <ModalHeader text={'Edit info'} />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ModalTextField
            hasError={titleHasError}
            errorText={'Bracket name is required'}
            placeholderText={'Bracket name...'}
            input={props.bracketData.title}
            setInput={(val) =>
              props.setBracketData({ ...props.bracketData, title: val })
            }
            setHasError={setTitleHasError}
          />
          <div className="tw-mb-20"></div>
          <DatePicker
            month={props.bracketData.month}
            year={props.bracketData.year}
            handleMonthChange={(month) =>
              props.setBracketData({ ...props.bracketData, month })
            }
            handleYearChange={(year) =>
              props.setBracketData({ ...props.bracketData, year })
            }
            showTitle={false}
            onHasError={onDateError}
            onErrorCleared={onDateErrorCleared}
          />
          <div className={'tw-mb-30'}></div>
          <ConfirmButton
            disabled={loading || titleHasError || dateHasError}
            onClick={onEditBracket}
          >
            {'Save'}
          </ConfirmButton>
          <CancelButton onClick={() => props.setShow(false)} />
        </div>
      </div>
    </Modal>
  )
}
