import * as React from 'react'
import { useState } from 'react'
import { bracketApi } from '../../../brackets/shared/api/bracketApi'
import addClickHandlers from '../../addClickHandlers'
import { Modal } from '../../Modal'
import { ModalHeader } from '../../ModalHeader'
import { ModalTextField } from '../../ModalTextFields'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { DatePicker } from '../../../brackets/shared/components/DatePicker'

export const EditBracketModal = () => {
  const [bracketId, setBracketId] = useState<number | null>(null)
  const [loading, setLoading] = useState(false)
  const [title, setTitle] = useState('')
  const [titleHasError, setTitleHasError] = useState(false)
  const [month, setMonth] = useState('')
  const [year, setYear] = useState('')
  // const [date, setDate] = useState('')
  const [dateHasError, setDateHasError] = useState(false)
  const [show, setShow] = useState(false)
  addClickHandlers({
    buttonClassName: 'wpbb-edit-bracket-button',
    onButtonClick: (b) => {
      setTitle(b.dataset.bracketTitle)
      // setDate(b.dataset.bracketDate)
      setBracketId(parseInt(b.dataset.bracketId))
      setShow(true)
    },
  })

  const resetState = () => {
    setBracketId(null)
    setLoading(false)
    setTitle('')
    setShow(false)
  }

  const onEditBracket = () => {
    if (!title) {
      setTitleHasError(true)
      return
    }
    // if (!date) {
    //   setDateHasError(true)
    //   return
    // }
    if (!month || !year) {
      setDateHasError(true)
      return
    }
    setLoading(true)
    bracketApi
      .updateBracket(bracketId, {
        title: title,
        date: `${month} ${year}`,
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
    <Modal show={show} setShow={setShow}>
      <div className="tw-flex tw-flex-col">
        <ModalHeader text={'Edit info'} />
        <div className="tw-flex tw-flex-col tw-gap-10">
          <ModalTextField
            hasError={titleHasError}
            errorText={'Bracket name is required'}
            placeholderText={'Bracket name...'}
            input={title}
            setInput={setTitle}
            setHasError={setTitleHasError}
          />
          <div className="tw-mb-20"></div>
          <DatePicker
            handleMonthChange={(month) => setMonth(month)}
            handleYearChange={(year) => setYear(year)}
            showTitle={false}
            // backgroundColorClass={'tw-bg-greyBlue'}
            backgroundColorClass={'tw-bg-lightGreyBlue'}
          />
          {/* <ModalTextField
            hasError={dateHasError}
            errorText={'Date is required'}
            placeholderText={'Date...'}
            input={date}
            setInput={setDate}
            setHasError={setDateHasError}
          /> */}
          <div className={'tw-mb-30'}></div>
          <ConfirmButton
            disabled={loading || titleHasError}
            onClick={onEditBracket}
          >
            {'Save'}
          </ConfirmButton>
          <CancelButton onClick={() => setShow(false)} />
        </div>
      </div>
    </Modal>
  )
}
