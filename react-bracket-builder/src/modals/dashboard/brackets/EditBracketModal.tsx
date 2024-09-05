import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { ModalTextField } from '../../ModalTextFields'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { DatePicker } from '../../../brackets/shared/components/DatePicker'

export const EditBracketModal = (props: {
  show: boolean
  setShow: (show: boolean) => void
  resetState: () => void
  bracketId: number
  setBracketId: (id: number) => void
  bracketTitle: string
  setBracketTitle: (title: string) => void
  bracketMonth: string
  setBracketMonth: (month: string) => void
  bracketYear: string
  setBracketYear: (year: string) => void
}) => {
  const [loading, setLoading] = useState(false)
  const [titleHasError, setTitleHasError] = useState(false)
  const [dateHasError, setDateHasError] = useState(false)

  addClickHandlers({
    buttonClassName: 'wpbb-edit-bracket-button',
    onButtonClick: (b) => {
      props.setBracketTitle(b.dataset.bracketTitle)
      props.setBracketMonth(b.dataset.bracketMonth)
      props.setBracketYear(b.dataset.bracketYear)
      props.setBracketId(parseInt(b.dataset.bracketId))
      props.setShow(true)
    },
  })

  const resetState = () => {
    props.resetState()
    setLoading(false)
    setTitleHasError(false)
    setDateHasError(false)
  }

  const onDateError = (error: string) => {
    setDateHasError(true)
  }

  const onDateErrorCleared = () => {
    setDateHasError(false)
  }

  const onEditBracket = () => {
    if (!props.bracketTitle) {
      setTitleHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateBracket(props.bracketId, {
        title: props.bracketTitle,
        month: props.bracketMonth,
        year: props.bracketYear,
      })
      .then((res) => {
        window.location.reload()
      })
      .catch((err) => {
        console.error(err)
      })
      .finally(() => {
        resetState()
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
            input={props.bracketTitle}
            setInput={props.setBracketTitle}
            setHasError={setTitleHasError}
          />
          <div className="tw-mb-20"></div>
          <DatePicker
            month={props.bracketMonth}
            year={props.bracketYear}
            handleMonthChange={(month) => props.setBracketMonth(month)}
            handleYearChange={(year) => props.setBracketYear(year)}
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
